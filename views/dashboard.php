<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<?php
$annual = $kpis['annual_summary'] ?? [];
$year   = (int)($annual['year'] ?? date('Y'));
$alerts = $annual['alerts'] ?? [];

$ap  = (float)($annual['annual_profit']      ?? 0);
$mp  = (float)($annual['margin_pct']         ?? 0);
$mr  = (float)($annual['monthly_revenue']    ?? 0);
$mp2 = (float)($annual['monthly_profit']     ?? 0);
$mgp = (float)($annual['monthly_growth_pct'] ?? 0);
$ep  = (float)($annual['expense_rate_pct']   ?? 0);
$ar  = (float)($annual['annual_revenue']     ?? 0);
$rr  = (float)($annual['run_rate_annual']    ?? 0);
$mrrVal = (float)($mrr ?? 0);
$arrVal = (float)($arr ?? 0);

// Abonnements actifs (fix: getAll() sans array)
$activeSubs = [];
try {
    require_once __DIR__ . '/../models/Subscription.php';
    $subM = new Subscription();
    $allSubs = $subM->getAll(200);
    $activeSubs = array_slice(
        array_filter($allSubs, fn($s) => !empty($s['is_active']) && (empty($s['end_date']) || $s['end_date'] >= date('Y-m-d'))),
        0, 6
    );
} catch (Throwable $e) {}
$subPeriodLabels = ['monthly'=>'Mensuelle','quarterly'=>'Trimestrielle','annual'=>'Annuelle','one_time'=>'Unique'];
$subPeriodColors = ['monthly'=>'#7c3aed','quarterly'=>'#2563eb','annual'=>'#0891b2','one_time'=>'#64748b'];
?>

<div id="main-wrap" class="flex-1 flex flex-col overflow-hidden">

  <div class="topbar flex items-center justify-between px-6 h-14 flex-shrink-0 sticky top-0 z-20">
    <div style="display:flex;align-items:center;gap:8px;">
      <button id="menu-toggle" class="lg:hidden" style="background:none;border:none;cursor:pointer;padding:4px;">
        <span class="material-icons-round" style="color:#64748b;font-size:20px;">menu</span>
      </button>
      <span style="font-size:15px;font-weight:700;color:#0f172a;">Tableau de bord</span>
      <span style="font-size:11px;color:#94a3b8;background:#f1f5f9;padding:2px 8px;border-radius:10px;font-weight:600;"><?= $year ?></span>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
      <?php if (!empty($user['avatar'])): ?>
      <img src="<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>" style="width:28px;height:28px;border-radius:50%;object-fit:cover;" alt="">
      <?php endif; ?>
      <span style="font-size:13px;font-weight:500;color:#475569;"><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </div>

  <main class="flex-1 overflow-y-auto" style="padding:20px 24px;">

    <?php if (!empty($alerts)): ?>
    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:16px;">
      <?php foreach ($alerts as $alert): ?>
      <span class="alert-pill">
        <span class="material-icons-round" style="font-size:12px;">warning_amber</span>
        <?= htmlspecialchars($alert, ENT_QUOTES, 'UTF-8') ?>
      </span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── Ligne 1 : CA / Résultat annuel ──────────────────────────────── -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:12px;">

      <div class="stat-card accent-blue">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:8px;">CA encaissé <?= $year ?></div>
        <div style="font-size:26px;font-weight:800;color:#0f172a;line-height:1;"><?= number_format($ar, 0, ',', ' ') ?> €</div>
        <div style="font-size:11px;color:#64748b;margin-top:6px;">Paiements reçus cette année</div>
      </div>

      <div class="stat-card accent-sky">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:8px;">Run-rate annuel</div>
        <div style="font-size:26px;font-weight:800;color:#0f172a;line-height:1;"><?= number_format($rr, 0, ',', ' ') ?> €</div>
        <div style="font-size:11px;color:#64748b;margin-top:6px;">Projection rythme actuel</div>
      </div>

      <div class="stat-card <?= $ap >= 0 ? 'accent-green' : 'accent-red' ?>">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:8px;">Résultat annuel</div>
        <div style="font-size:26px;font-weight:800;color:<?= $ap >= 0 ? '#16a34a' : '#dc2626' ?>;line-height:1;"><?= ($ap >= 0 ? '+' : '') . number_format($ap, 0, ',', ' ') ?> €</div>
        <div style="font-size:11px;color:<?= $ap >= 0 ? '#16a34a' : '#dc2626' ?>;margin-top:6px;"><?= $ap >= 0 ? 'Rentable' : 'Déficitaire' ?></div>
      </div>

      <div class="stat-card <?= $mp >= 20 ? 'accent-green' : ($mp >= 5 ? 'accent-amber' : 'accent-red') ?>">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:8px;">Marge nette</div>
        <div style="font-size:26px;font-weight:800;color:<?= $mp >= 20 ? '#16a34a' : ($mp >= 5 ? '#d97706' : '#dc2626') ?>;line-height:1;"><?= ($mp > 0 ? '+' : '') . number_format($mp, 1, ',', '.') ?> %</div>
        <div style="font-size:11px;color:#64748b;margin-top:6px;">Résultat / CA encaissé</div>
      </div>

    </div>

    <!-- ── Ligne 2 : Mois en cours + récurrents ─────────────────────────── -->
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:16px;">

      <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:6px;">CA du mois</div>
        <div style="font-size:20px;font-weight:800;color:#0f172a;line-height:1;"><?= number_format($mr, 0, ',', ' ') ?> €</div>
        <div style="font-size:11px;margin-top:5px;font-weight:600;color:<?= $mgp >= 0 ? '#16a34a' : '#dc2626' ?>;">
          <?= ($mgp >= 0 ? '↑' : '↓') . ' ' . abs(round($mgp, 1)) ?>% vs préc.
        </div>
      </div>

      <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:6px;">Résultat mois</div>
        <div style="font-size:20px;font-weight:800;color:<?= $mp2 >= 0 ? '#16a34a' : '#dc2626' ?>;line-height:1;"><?= ($mp2 >= 0 ? '+' : '') . number_format($mp2, 0, ',', ' ') ?> €</div>
        <div style="font-size:11px;margin-top:5px;font-weight:600;color:<?= $mp2 >= 0 ? '#16a34a' : '#dc2626' ?>;"><?= $mp2 >= 0 ? 'Mois rentable' : 'Mois déficitaire' ?></div>
      </div>

      <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:6px;">Taux de charges</div>
        <div style="font-size:20px;font-weight:800;color:#0f172a;line-height:1;"><?= number_format($ep, 1, ',', '.') ?> %</div>
        <div style="font-size:11px;margin-top:5px;font-weight:500;color:#d97706;">Charges / CA</div>
      </div>

      <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;border-left:3px solid #7c3aed;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:6px;">MRR</div>
        <div style="font-size:20px;font-weight:800;color:#7c3aed;line-height:1;"><?= number_format($mrrVal, 2, ',', ' ') ?> €</div>
        <div style="font-size:11px;margin-top:5px;font-weight:500;color:#7c3aed;">Revenus récurrents / mois</div>
      </div>

      <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;border-left:3px solid #2563eb;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:6px;">ARR</div>
        <div style="font-size:20px;font-weight:800;color:#2563eb;line-height:1;"><?= number_format($arrVal, 0, ',', ' ') ?> €</div>
        <div style="font-size:11px;margin-top:5px;font-weight:500;color:#2563eb;">Revenus récurrents annualisés</div>
      </div>

    </div>

    <!-- ── Graphiques ──────────────────────────────────────────────────── -->
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;margin-bottom:16px;">
      <div class="panel">
        <div class="panel-head">
          <span class="material-icons-round" style="font-size:15px;color:#3b82f6;">show_chart</span>
          Évolution du CA — 12 mois
        </div>
        <div style="padding:16px;">
          <canvas id="revenueChart" style="height:190px;"></canvas>
        </div>
      </div>
      <div class="panel">
        <div class="panel-head">
          <span class="material-icons-round" style="font-size:15px;color:#f59e0b;">pie_chart</span>
          Dépenses par catégorie
        </div>
        <div style="padding:16px;">
          <?php if (!empty($annual['expense_categories'])): ?>
          <canvas id="expenseChart" style="height:175px;"></canvas>
          <?php else: ?>
          <div style="height:175px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;color:#cbd5e1;">
            <span class="material-icons-round" style="font-size:32px;">bar_chart</span>
            <span style="font-size:12px;">Aucune dépense enregistrée</span>
            <a href="<?= APP_URL ?>/expenses" style="font-size:12px;color:#3b82f6;text-decoration:none;font-weight:600;">Ajouter →</a>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- ── Tables ─────────────────────────────────────────────────────── -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">

      <!-- Top clients -->
      <div class="panel" style="overflow:hidden;">
        <div class="panel-head">
          <span class="material-icons-round" style="font-size:15px;color:#3b82f6;">star</span>
          Top clients
          <?php if (!empty($kpis['top_tiers'])): ?>
          <span style="margin-left:auto;font-size:11px;color:#94a3b8;"><?= count($kpis['top_tiers'] ?? []) ?> clients</span>
          <?php endif; ?>
        </div>
        <table class="data-table">
          <thead><tr>
            <th>Client</th>
            <th style="text-align:right;">CA</th>
            <th style="text-align:right;">Part</th>
          </tr></thead>
          <tbody>
            <?php
            $topTiers = $kpis['top_tiers'] ?? [];
            $totalRev = max(1, $ar);
            foreach (array_slice($topTiers, 0, 6) as $i => $t):
              $share = $totalRev > 0 ? round(((float)$t['revenue'] / $totalRev) * 100, 1) : 0;
              $barW  = max(4, (int)$share);
            ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:8px;">
                  <span style="width:18px;height:18px;border-radius:50%;background:#2563eb;color:#fff;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><?= $i+1 ?></span>
                  <a href="<?= APP_URL ?>/tiers/<?= (int)$t['id'] ?>" style="font-weight:600;color:#0f172a;text-decoration:none;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;max-width:130px;">
                    <?= htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </div>
              </td>
              <td style="text-align:right;font-weight:700;white-space:nowrap;"><?= number_format((float)$t['revenue'], 0, ',', ' ') ?> €</td>
              <td style="text-align:right;">
                <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px;">
                  <div style="width:40px;height:4px;background:#f1f5f9;border-radius:2px;overflow:hidden;">
                    <div style="width:<?= $barW ?>%;height:100%;background:#3b82f6;border-radius:2px;"></div>
                  </div>
                  <span style="font-size:11px;color:#64748b;white-space:nowrap;"><?= $share ?>%</span>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($topTiers)): ?>
            <tr>
              <td colspan="3" style="text-align:center;padding:32px 16px;">
                <div style="color:#cbd5e1;font-size:12px;">
                  <span class="material-icons-round" style="display:block;font-size:28px;margin-bottom:6px;">groups</span>
                  Aucun encaissement enregistré
                </div>
              </td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Abonnements actifs -->
      <div class="panel" style="overflow:hidden;">
        <div class="panel-head">
          <span class="material-icons-round" style="font-size:15px;color:#7c3aed;">autorenew</span>
          Abonnements actifs
          <?php if (!empty($activeSubs)): ?>
          <span style="margin-left:auto;font-size:11px;font-weight:600;color:#7c3aed;background:#f5f3ff;padding:2px 8px;border-radius:10px;"><?= count($activeSubs) ?></span>
          <?php endif; ?>
        </div>
        <?php if (!empty($activeSubs)): ?>
        <table class="data-table">
          <thead><tr>
            <th>Client</th>
            <th>Service</th>
            <th style="text-align:right;">Montant</th>
            <th>Fréq.</th>
          </tr></thead>
          <tbody>
            <?php foreach ($activeSubs as $sub):
              $period = (string)($sub['recurrence'] ?? 'monthly');
              $pColor = $subPeriodColors[$period] ?? '#64748b';
            ?>
            <tr>
              <td style="font-weight:600;max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                <?= htmlspecialchars($sub['tiers_name'] ?? '–', ENT_QUOTES, 'UTF-8') ?>
              </td>
              <td style="color:#334155;max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;">
                <?= htmlspecialchars($sub['label'] ?? '–', ENT_QUOTES, 'UTF-8') ?>
              </td>
              <td style="text-align:right;font-weight:700;white-space:nowrap;"><?= number_format((float)($sub['amount'] ?? 0), 2, ',', ' ') ?> €</td>
              <td>
                <span style="font-size:10px;font-weight:600;color:<?= $pColor ?>;background:<?= $pColor ?>18;padding:2px 6px;border-radius:8px;white-space:nowrap;">
                  <?= $subPeriodLabels[$period] ?? $period ?>
                </span>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr style="background:#f8fafc;">
              <td colspan="2" style="padding:8px 12px;font-size:11px;font-weight:700;color:#64748b;">MRR total</td>
              <td style="text-align:right;padding:8px 12px;font-weight:800;color:#7c3aed;white-space:nowrap;"><?= number_format($mrrVal, 2, ',', ' ') ?> €</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
        <?php else: ?>
        <div style="padding:32px 16px;text-align:center;">
          <span class="material-icons-round" style="display:block;font-size:28px;color:#cbd5e1;margin-bottom:8px;">autorenew</span>
          <div style="font-size:12px;color:#94a3b8;margin-bottom:10px;">Aucun abonnement actif</div>
          <a href="<?= APP_URL ?>/subscriptions" style="font-size:12px;font-weight:600;color:#7c3aed;text-decoration:none;">Gérer les abonnements →</a>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<?php
$revLabels = json_encode(array_column($kpis['revenue_evolution'] ?? [], 'month'));
$revValues = json_encode(array_map(fn($r) => (float)($r['revenue'] ?? 0), $kpis['revenue_evolution'] ?? []));
$expCats   = $annual['expense_categories'] ?? [];
$expLabels = json_encode(array_column($expCats, 'category'));
$expValues = json_encode(array_column($expCats, 'monthly_total'));
?>
<script>
(function () {
  const rc = document.getElementById('revenueChart');
  if (rc) {
    rc.style.width = '100%';
    const vals = <?= $revValues ?>;
    const hasData = vals.some(v => v > 0);
    new Chart(rc, {
      type: 'bar',
      data: {
        labels: <?= $revLabels ?>,
        datasets: [{
          data: vals,
          backgroundColor: vals.map((v, i) => i === vals.length - 1 ? 'rgba(37,99,235,.85)' : 'rgba(37,99,235,.25)'),
          borderColor: '#2563eb',
          borderRadius: 4,
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: { label: ctx => ' ' + ctx.raw.toLocaleString('fr-FR') + ' €' } }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { callback: v => v.toLocaleString('fr-FR') + ' €', font: { size: 11 } },
            grid: { color: '#f1f5f9' }
          },
          x: { grid: { display: false }, ticks: { font: { size: 10 } } }
        }
      }
    });
  }

  const ec = document.getElementById('expenseChart');
  if (ec) {
    ec.style.width = '100%';
    const colors = ['#2563eb','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316'];
    new Chart(ec, {
      type: 'doughnut',
      data: {
        labels: <?= $expLabels ?>,
        datasets: [{ data: <?= $expValues ?>, backgroundColor: colors, borderWidth: 2, borderColor: '#fff', hoverOffset: 4 }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '62%',
        plugins: {
          legend: { position: 'right', labels: { padding: 10, font: { size: 11 }, boxWidth: 10 } },
          tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.raw.toLocaleString('fr-FR') + ' €' } }
        }
      }
    });
  }
}());
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
