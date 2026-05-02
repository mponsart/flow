<?php
class DolibarrService
{
    private string $baseUrl;
    private string $apiKey;
    private PDO    $pdo;
    private int    $maxRetries = 3;

    public function __construct()
    {
        $dolibarrUrl = rtrim(DOLIBARR_URL, '/');
        $this->baseUrl = str_ends_with($dolibarrUrl, '/api/index.php')
            ? $dolibarrUrl
            : $dolibarrUrl . '/api/index.php';
        $this->apiKey  = DOLIBARR_API_KEY;
        $this->pdo     = getDB();
        $this->ensureSyncSchema();
    }

    public function syncAll(): array
    {
        $results = [];
        $results['tiers']    = $this->syncThirdParties();
        $results['services'] = $this->syncServices();
        $results['invoices'] = $this->syncInvoices();
        $results['payments'] = $this->syncPayments();
        $results['kpis']     = $this->recalculateKpis();
        return $results;
    }

    public function forceSync(): array
    {
        // Delete last sync timestamps to force full sync
        $this->pdo->exec("DELETE FROM settings WHERE key_name LIKE 'last_sync_%'");
        return $this->syncAll();
    }

    public function syncThirdParties(): array
    {
        $logId = $this->startLog('tiers');
        $processed = 0;
        $failed    = 0;
        $status    = 'success';
        $message   = 'Sync tiers OK';

        try {
            $page     = 0;
            $limit    = 100;

            do {
                $params = ['limit' => $limit, 'page' => $page, 'sortfield' => 't.rowid', 'sortorder' => 'ASC'];

                $data = $this->apiGet('/thirdparties', $params);
                if (empty($data)) break;

                foreach ($data as $item) {
                    try {
                        $this->upsertTiers($item);
                        $processed++;
                    } catch (Throwable $e) {
                        error_log('syncThirdParties item error: ' . $e->getMessage());
                        $failed++;
                    }
                }
                $page++;
            } while (count($data) === $limit);

            if ($failed > 0) {
                $status = 'warning';
                $message = "Sync tiers terminée avec $failed erreur(s)";
            }
            $this->setLastSync('tiers');
        } catch (Throwable $e) {
            $status = $this->isOptionalAccessError($e) ? 'warning' : 'error';
            $message = $e->getMessage();
            error_log('syncThirdParties error: ' . $e->getMessage());
        }

        $this->endLog($logId, $status, $message, $processed, $failed);
        return ['status' => $status, 'message' => $message, 'processed' => $processed, 'failed' => $failed];
    }

    public function syncServices(): array
    {
        $logId = $this->startLog('services');
        $processed = 0;
        $failed    = 0;
        $status    = 'success';
        $message   = 'Sync services OK';

        try {
            $page  = 0;
            $limit = 100;

            do {
                $data = $this->apiGet('/products', ['limit' => $limit, 'page' => $page, 'type' => 1]);
                if (empty($data)) break;

                foreach ($data as $item) {
                    try {
                        $this->upsertService($item);
                        $processed++;
                    } catch (Throwable $e) {
                        error_log('syncServices item error: ' . $e->getMessage());
                        $failed++;
                    }
                }
                $page++;
            } while (count($data) === $limit);

            if ($failed > 0) {
                $status = 'warning';
                $message = "Sync services terminée avec $failed erreur(s)";
            }
        } catch (Throwable $e) {
            $status = $this->isOptionalAccessError($e) ? 'warning' : 'error';
            $message = $this->isOptionalAccessError($e)
                ? 'Endpoint services refusé par Dolibarr; les services seront construits depuis les lignes de factures.'
                : $e->getMessage();
            error_log('syncServices error: ' . $e->getMessage());
        }

        $this->endLog($logId, $status, $message, $processed, $failed);
        return ['status' => $status, 'message' => $message, 'processed' => $processed, 'failed' => $failed];
    }

    public function syncInvoices(): array
    {
        $logId = $this->startLog('invoices');
        $processed = 0;
        $lineCount  = 0;
        $failed    = 0;
        $status    = 'success';
        $message   = 'Sync invoices OK';

        try {
            $page     = 0;
            $limit    = 100;

            do {
                $params = [
                    'limit' => $limit,
                    'page' => $page,
                    'sortfield' => 't.rowid',
                    'sortorder' => 'ASC',
                    'loadlinkedobjects' => 1,
                ];

                $data = $this->apiGet('/invoices', $params);
                if (empty($data)) break;

                foreach ($data as $item) {
                    try {
                        $lineCount += $this->upsertInvoice($item);
                        $processed++;
                    } catch (Throwable $e) {
                        error_log('syncInvoices item error: ' . $e->getMessage());
                        $failed++;
                    }
                }
                $page++;
            } while (count($data) === $limit);

            if ($failed > 0) {
                $status = 'warning';
                $message = "Sync invoices terminée avec $failed erreur(s)";
            } else {
                $message = "Sync invoices OK ($lineCount ligne(s))";
            }
            $this->setLastSync('invoices');
        } catch (Throwable $e) {
            $status = 'error';
            $message = $e->getMessage();
            error_log('syncInvoices error: ' . $e->getMessage());
        }

        $this->endLog($logId, $status, $message, $processed, $failed);
        return ['status' => $status, 'message' => $message, 'processed' => $processed, 'failed' => $failed];
    }

    public function syncPayments(): array
    {
        $logId = $this->startLog('payments');
        $processed = 0;
        $failed    = 0;
        $status    = 'success';
        $message   = 'Sync payments OK';

        try {
            $stmt = $this->pdo->query(
                'SELECT id, dolibarr_id, tiers_id FROM invoices WHERE dolibarr_id IS NOT NULL ORDER BY dolibarr_id ASC'
            );
            $invoices = $stmt->fetchAll();

            if (empty($invoices)) {
                $status = 'warning';
                $message = 'Aucune facture locale disponible pour synchroniser les paiements';
            }

            foreach ($invoices as $invoice) {
                try {
                    $data = $this->apiGet('/invoices/' . (int)$invoice['dolibarr_id'] . '/payments');
                } catch (Throwable $e) {
                    if ($this->isMissingPaymentList($e)) {
                        continue;
                    }

                    throw $e;
                }

                if (empty($data)) {
                    continue;
                }

                foreach ($data as $item) {
                    try {
                        $this->upsertPayment($item, (int)$invoice['id'], $invoice['tiers_id'] ? (int)$invoice['tiers_id'] : null);
                        $processed++;
                    } catch (Throwable $e) {
                        error_log('syncPayments item error: ' . $e->getMessage());
                        $failed++;
                    }
                }
            }

            if ($failed > 0) {
                $status = 'warning';
                $message = "Sync payments terminée avec $failed erreur(s)";
            }
            $this->setLastSync('payments');
        } catch (Throwable $e) {
            $status = 'error';
            $message = $e->getMessage();
            error_log('syncPayments error: ' . $e->getMessage());
        }

        $this->endLog($logId, $status, $message, $processed, $failed);
        return ['status' => $status, 'message' => $message, 'processed' => $processed, 'failed' => $failed];
    }

    private function apiGet(string $endpoint, array $params = []): array
    {
        $url = $this->baseUrl . $endpoint;
        if ($params) {
            $url .= '?' . http_build_query($params);
        }

        $attempt = 0;
        while ($attempt < $this->maxRetries) {
            $context = stream_context_create([
                'http' => [
                    'method'  => 'GET',
                    'header'  => "User-Agent: FlowSync/1.0 (+Dolibarr API)\r\nDOLAPIKEY: {$this->apiKey}\r\nAccept: application/json\r\n",
                    'timeout' => 30,
                    'ignore_errors' => true,
                ],
            ]);

            $response = @file_get_contents($url, false, $context);
            $httpCode = 0;
            if (isset($http_response_header)) {
                preg_match('/HTTP\/\S+\s+(\d+)/', $http_response_header[0], $m);
                $httpCode = (int)($m[1] ?? 0);
            }

            if ($response !== false && $httpCode === 200) {
                $decoded = json_decode($response, true);
                if (!is_array($decoded)) {
                    throw new RuntimeException("Réponse Dolibarr invalide sur $endpoint");
                }

                if (isset($decoded['error']) || isset($decoded['errors'])) {
                    throw new RuntimeException('Erreur Dolibarr sur ' . $endpoint . ' : ' . $this->summarizeApiResponse($decoded));
                }

                if ($this->isList($decoded)) {
                    return $decoded;
                }

                if ($this->isAssociativeList($decoded)) {
                    return array_values($decoded);
                }

                return [];
            }

            $attempt++;
            if ($attempt < $this->maxRetries) {
                sleep(2 * $attempt);
            }
        }

        $details = $response === false ? 'aucune réponse' : $this->summarizeApiResponse(json_decode($response, true) ?: $response);
        $hint = $this->httpErrorHint($httpCode);
        throw new RuntimeException("Dolibarr API $endpoint indisponible ou refusée (HTTP $httpCode, $details). $hint");
    }

    private function httpErrorHint(int $httpCode): string
    {
        return match ($httpCode) {
            401 => 'Vérifiez DOLIBARR_API_KEY.',
            403 => 'Vérifiez que l’API REST est activée dans Dolibarr, que la clé API appartient à un utilisateur autorisé, et que cPanel/o2switch ne bloque pas la requête.',
            404 => 'Vérifiez DOLIBARR_URL : l’URL doit pointer vers la racine Dolibarr ou vers /api/index.php.',
            default => 'Vérifiez la configuration Dolibarr et les logs serveur.',
        };
    }

    private function summarizeApiResponse(mixed $data): string
    {
        if (is_array($data)) {
            $message = $data['message'] ?? $data['errors'] ?? $data;
            if (isset($data['error'])) {
                $message = is_array($data['error'])
                    ? ($data['error']['message'] ?? $data['error'])
                    : $data['error'];
            }

            return substr(json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'réponse JSON', 0, 300);
        }

        return substr((string)$data, 0, 300);
    }

    private function isList(array $data): bool
    {
        return $data === [] || array_keys($data) === range(0, count($data) - 1);
    }

    private function isAssociativeList(array $data): bool
    {
        foreach ($data as $value) {
            if (!is_array($value)) {
                return false;
            }
        }

        return $data !== [];
    }

    private function ensureSyncSchema(): void
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT COUNT(*)
                 FROM information_schema.STATISTICS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = "products"
                   AND INDEX_NAME = "uq_products_ref_type"'
            );
            $stmt->execute();

            if ((int)$stmt->fetchColumn() === 0) {
                $this->pdo->exec('ALTER TABLE products ADD UNIQUE KEY uq_products_ref_type (ref, type)');
            }
        } catch (Throwable $e) {
            error_log('ensureSyncSchema error: ' . $e->getMessage());
        }
    }

    private function isOptionalAccessError(Throwable $e): bool
    {
        return str_contains($e->getMessage(), 'HTTP 403');
    }

    private function isMissingPaymentList(Throwable $e): bool
    {
        return str_contains($e->getMessage(), 'HTTP 404')
            || str_contains($e->getMessage(), 'HTTP 405');
    }

    private function recalculateKpis(): array
    {
        try {
            require_once __DIR__ . '/KPIService.php';

            $kpis = (new KPIService())->getAll();
            foreach ($kpis as $key => $value) {
                $this->pdo->prepare(
                    'INSERT INTO kpi_cache (key_name, value, period, calculated_at)
                     VALUES (?, ?, "current", NOW())
                     ON DUPLICATE KEY UPDATE value=VALUES(value), calculated_at=NOW()'
                )->execute([$key, json_encode($value)]);
            }

            return ['status' => 'success', 'message' => 'KPI recalculés', 'processed' => count($kpis), 'failed' => 0];
        } catch (Throwable $e) {
            error_log('recalculateKpis error: ' . $e->getMessage());
            return ['status' => 'warning', 'message' => $e->getMessage(), 'processed' => 0, 'failed' => 1];
        }
    }

    private function upsertTiers(array $data): void
    {
        if (empty($data['id'])) return;

        $stmt = $this->pdo->prepare(
            'INSERT INTO tiers (dolibarr_id, name, email, phone, address, is_active, created_at, updated_at)
             VALUES (:did, :name, :email, :phone, :address, :active, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
               name=VALUES(name), email=VALUES(email), phone=VALUES(phone),
               address=VALUES(address), is_active=VALUES(is_active), updated_at=NOW()'
        );
        $stmt->execute([
            'did'     => $data['id'],
            'name'    => substr($data['name'] ?? '', 0, 255),
            'email'   => substr($data['email'] ?? '', 0, 255),
            'phone'   => substr($data['phone'] ?? '', 0, 50),
            'address' => substr(($data['address'] ?? '') . ' ' . ($data['zip'] ?? '') . ' ' . ($data['town'] ?? ''), 0, 500),
            'active'  => ($data['status'] ?? 1) == 1 ? 1 : 0,
        ]);
    }

    private function upsertService(array $data): void
    {
        if (empty($data['id'])) return;

        $stmt = $this->pdo->prepare(
            'INSERT INTO products (dolibarr_id, ref, label, price, type, created_at, updated_at)
             VALUES (:did, :ref, :label, :price, :type, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
               ref=VALUES(ref), label=VALUES(label), price=VALUES(price),
               type=VALUES(type), updated_at=NOW()'
        );
        $stmt->execute([
            'did'   => $data['id'],
            'ref'   => substr($data['ref'] ?? '', 0, 100),
            'label' => substr($data['label'] ?? '', 0, 255),
            'price' => (float)($data['price'] ?? 0),
            'type'  => 1,
        ]);
    }

    private function upsertInvoice(array $data): int
    {
        if (empty($data['id'])) return 0;

        // Resolve tiers_id
        $tiersId = null;
        if (!empty($data['socid'])) {
            $s = $this->pdo->prepare('SELECT id FROM tiers WHERE dolibarr_id = ?');
            $s->execute([$data['socid']]);
            $t = $s->fetch();
            $tiersId = $t ? $t['id'] : null;
        }

        $dateInvoice = !empty($data['date']) ? date('Y-m-d', (int)$data['date']) : null;
        $dateDue     = !empty($data['date_lim_reglement']) ? date('Y-m-d', (int)$data['date_lim_reglement']) : null;
        $datePaid    = !empty($data['date_closing']) ? date('Y-m-d', (int)$data['date_closing']) : null;
        $isOverdue   = ($dateDue && $dateDue < date('Y-m-d') && ($data['statut'] ?? 0) != 2) ? 1 : 0;

        $stmt = $this->pdo->prepare(
            'INSERT INTO invoices (dolibarr_id, ref, tiers_id, date_invoice, date_due, date_paid, total_ht, total_ttc, status, is_overdue, created_at, updated_at)
             VALUES (:did, :ref, :tiers, :di, :dd, :dp, :tht, :ttc, :status, :over, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
               ref=VALUES(ref), tiers_id=VALUES(tiers_id), date_invoice=VALUES(date_invoice),
               date_due=VALUES(date_due), date_paid=VALUES(date_paid), total_ht=VALUES(total_ht),
               total_ttc=VALUES(total_ttc), status=VALUES(status), is_overdue=VALUES(is_overdue), updated_at=NOW()'
        );
        $stmt->execute([
            'did'    => $data['id'],
            'ref'    => substr($data['ref'] ?? '', 0, 100),
            'tiers'  => $tiersId,
            'di'     => $dateInvoice,
            'dd'     => $dateDue,
            'dp'     => $datePaid,
            'tht'    => (float)($data['total_ht'] ?? 0),
            'ttc'    => (float)($data['total_ttc'] ?? 0),
            'status' => (int)($data['statut'] ?? 0),
            'over'   => $isOverdue,
        ]);

        // Sync invoice lines
        $invStmt = $this->pdo->prepare('SELECT id FROM invoices WHERE dolibarr_id = ?');
        $invStmt->execute([$data['id']]);
        $inv = $invStmt->fetch();
        if ($inv) {
            return $this->syncInvoiceLines((int)$data['id'], (int)$inv['id'], $data['lines'] ?? null);
        }

        return 0;
    }

    private function syncInvoiceLines(int $dolibarrInvoiceId, int $localInvoiceId, ?array $lines): int
    {
        if ($lines === null) {
            try {
                $lines = $this->apiGet('/invoices/' . $dolibarrInvoiceId . '/lines');
            } catch (Throwable $e) {
                error_log('syncInvoiceLines error for invoice ' . $dolibarrInvoiceId . ': ' . $e->getMessage());
                return 0;
            }
        }

        $this->pdo->prepare('DELETE FROM invoice_lines WHERE invoice_id = ?')->execute([$localInvoiceId]);

        $count = 0;
        foreach ($lines as $line) {
            $this->upsertInvoiceLine($localInvoiceId, $line);
            $count++;
        }

        return $count;
    }

    private function upsertInvoiceLine(int $invoiceId, array $line): void
    {
        $productId = null;
        if (!empty($line['fk_product'])) {
            $s = $this->pdo->prepare('SELECT id FROM products WHERE dolibarr_id = ?');
            $s->execute([$line['fk_product']]);
            $p = $s->fetch();
            $productId = $p ? $p['id'] : null;

            if (!$productId) {
                $productId = $this->upsertServiceFromInvoiceLine($line);
            }
        } else {
            $productId = $this->upsertServiceFromInvoiceLine($line);
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO invoice_lines (invoice_id, product_id, description, qty, unit_price, total_ht)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE qty=VALUES(qty), unit_price=VALUES(unit_price), total_ht=VALUES(total_ht)'
        );
        $stmt->execute([
            $invoiceId,
            $productId,
            substr($line['desc'] ?? $line['product_label'] ?? '', 0, 500),
            (float)($line['qty'] ?? 1),
            (float)($line['subprice'] ?? 0),
            (float)($line['total_ht'] ?? 0),
        ]);
    }

    private function upsertServiceFromInvoiceLine(array $line): ?int
    {
        $dolibarrProductId = (int)($line['fk_product'] ?? 0);
        $label = $line['product_label'] ?? $line['label'] ?? $line['desc'] ?? ('Service #' . $dolibarrProductId);
        $label = trim(strip_tags((string)$label));
        if ($label === '') {
            return null;
        }

        $ref = $line['product_ref'] ?? $line['ref'] ?? '';
        if (!$ref) {
            $ref = $dolibarrProductId > 0 ? 'DOL-' . $dolibarrProductId : 'LINE-' . substr(sha1($label), 0, 12);
        }

        $this->pdo->prepare(
            'INSERT INTO products (dolibarr_id, ref, label, price, type, created_at, updated_at)
             VALUES (:did, :ref, :label, :price, :type, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
               ref=VALUES(ref), label=VALUES(label), price=VALUES(price), type=VALUES(type), updated_at=NOW()'
        )->execute([
            'did'   => $dolibarrProductId > 0 ? $dolibarrProductId : null,
            'ref'   => substr($ref, 0, 100),
            'label' => substr($label, 0, 255),
            'price' => (float)($line['subprice'] ?? 0),
            'type'  => 1,
        ]);

        if ($dolibarrProductId > 0) {
            $stmt = $this->pdo->prepare('SELECT id FROM products WHERE dolibarr_id = ?');
            $stmt->execute([$dolibarrProductId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT id FROM products WHERE ref = ? AND type = 1');
            $stmt->execute([substr($ref, 0, 100)]);
        }
        $id = $stmt->fetchColumn();

        return $id ? (int)$id : null;
    }

    private function upsertPayment(array $data, ?int $localInvoiceId = null, ?int $localTiersId = null): void
    {
        if (empty($data['id']) && empty($data['rowid'])) return;

        $invoiceId = $localInvoiceId;
        $tiersId = $localTiersId;
        if (!$invoiceId && !empty($data['fk_facture'])) {
            $s = $this->pdo->prepare('SELECT id, tiers_id FROM invoices WHERE dolibarr_id = ?');
            $s->execute([$data['fk_facture']]);
            $inv = $s->fetch();
            if ($inv) {
                $invoiceId = $inv['id'];
                $tiersId = $inv['tiers_id'] ? (int)$inv['tiers_id'] : null;
            }
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO payments (dolibarr_id, invoice_id, tiers_id, amount, date_payment, method, method_label, created_at)
             VALUES (:did, :inv, :tiers, :amount, :date, :method, :mlabel, NOW())
             ON DUPLICATE KEY UPDATE
               amount=VALUES(amount), date_payment=VALUES(date_payment),
               method=VALUES(method), method_label=VALUES(method_label)'
        );

        $methodLabel = $data['payment_code'] ?? $data['type_libelle'] ?? '';
        $method = $this->detectMethod($data['payment_code'] ?? '', $methodLabel);
        $datePayment = $data['datepaye'] ?? $data['date'] ?? $data['date_payment'] ?? null;

        $stmt->execute([
            'did'    => $data['id'] ?? $data['rowid'],
            'inv'    => $invoiceId,
            'tiers'  => $tiersId,
            'amount' => (float)($data['amount'] ?? $data['amount_payment'] ?? 0),
            'date'   => $datePayment ? date('Y-m-d', (int)$datePayment) : null,
            'method' => $method,
            'mlabel' => $methodLabel,
        ]);
    }

    private function detectMethod(string $code, string $label): string
    {
        $codeUpper  = strtoupper($code);
        $labelLower = strtolower($label);

        if (in_array($codeUpper, ['CB', 'CARTE', 'CREDIT_CARD', 'VIS', 'MC'], true)) return 'CB';
        if (in_array($codeUpper, ['VIR', 'VIREMENT', 'TRANSFER', 'TRF'], true)) return 'virement';
        if (in_array($codeUpper, ['CHQ', 'CHEQUE', 'CHECK'], true)) return 'chèque';
        if (in_array($codeUpper, ['ESP', 'CASH', 'ESPECES'], true)) return 'espèces';

        if (str_contains($labelLower, 'carte') || str_contains($labelLower, 'cb') || str_contains($labelLower, 'visa')) return 'CB';
        if (str_contains($labelLower, 'virement') || str_contains($labelLower, 'transfer')) return 'virement';
        if (str_contains($labelLower, 'chèque') || str_contains($labelLower, 'cheque')) return 'chèque';
        if (str_contains($labelLower, 'espèces') || str_contains($labelLower, 'especes') || str_contains($labelLower, 'cash')) return 'espèces';

        return 'inconnu';
    }

    private function getLastSync(string $entity): ?string
    {
        $stmt = $this->pdo->prepare('SELECT value FROM settings WHERE key_name = ?');
        $stmt->execute(["last_sync_$entity"]);
        $row = $stmt->fetch();
        return $row ? $row['value'] : null;
    }

    private function setLastSync(string $entity): void
    {
        $this->pdo->prepare(
            'INSERT INTO settings (key_name, value, updated_at) VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE value=VALUES(value), updated_at=NOW()'
        )->execute(["last_sync_$entity", date('Y-m-d H:i:s')]);
    }

    private function startLog(string $entity): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO sync_logs (entity_type, status, message, records_processed, records_failed, started_at)
             VALUES (?, "running", "En cours...", 0, 0, NOW())'
        );
        $stmt->execute([$entity]);
        return (int)$this->pdo->lastInsertId();
    }

    private function endLog(int $logId, string $status, string $message, int $processed, int $failed): void
    {
        $this->pdo->prepare(
            'UPDATE sync_logs SET status=?, message=?, records_processed=?, records_failed=?, completed_at=NOW() WHERE id=?'
        )->execute([$status, $message, $processed, $failed, $logId]);
    }

    public function getRecentLogs(int $limit = 50): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM sync_logs ORDER BY started_at DESC LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
