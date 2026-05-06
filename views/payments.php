<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<?php
$methodCss = ['CB'=>'badge-blue','virement'=>'badge-green','chèque'=>'badge-amber','espèces'=>'badge-violet','inconnu'=>'badge-slate'];
$csrf = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<div id="main-wrap" class="flex-1 flex flex-col overflow-hidden">

  <div class="topbar flex items-center justify-between px-6 h-14 flex-shrink-0 sticky top-0 z-20">
    <div style="display:flex;align-items:center;gap:10px;">
      <button id="menu-toggle" class="lg:hidden" style="background:none;border:none;cursor:pointer;padding:4px;">
        <span class="material-icons-round" style="color:#64748b;font-size:20px;">menu</span>
      </button>
      <span style="font-size:15px;font-weight:700;color:#0f172a;">Paiements</span>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
      <?php if (!empty($user['avatar'])): ?>
      <img src="<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>" style="width:28px;height:28px;border-radius:50%;" alt="">
      <?php endif; ?>
      <span style="font-size:13px;font-weight:500;color:#475569;"><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </div>

  <main class="flex-1 overflow-y-auto" style="padding:20px 24px;" id="payments-page" v-cloak>

    <?php if (!empty($_GET['message'])): ?>
    <div class="flash-ok" style="margin-bottom:12px;">
      <span class="material-icons-round" style="font-size:16px;color:#16a34a;">check_circle</span>
      <?= htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
    <div class="flash-err" style="margin-bottom:12px;">
      <span class="material-icons-round" style="font-size:16px;color:#dc2626;">error</span>
      <?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>

    <!-- Add payment form -->
    <div class="panel" style="padding:20px;margin-bottom:16px;">
      <div style="font-size:13px;font-weight:600;color:#0f172a;margin-bottom:16px;display:flex;align-items:center;gap:6px;">
        <span class="material-icons-round" style="font-size:16px;color:#2563eb;">add_circle</span>
        Enregistrer un paiement
      </div>
      <form method="POST" action="<?= APP_URL ?>/payments/store" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;align-items:end;">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="field">
          <label>Client</label>
          <select name="tiers_id">
            <option value="">— Optionnel —</option>
            <?php foreach ($tiersAll as $t): ?>
            <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Montant (€) *</label><input type="number" name="amount" step="0.01" min="0.01" placeholder="0.00" required></div>
        <div class="field"><label>Date *</label><input type="date" name="date_payment" value="<?= date('Y-m-d') ?>" required></div>
        <div class="field">
          <label>Mode</label>
          <select name="method">
            <option value="virement">Virement</option>
            <option value="CB">CB</option>
            <option value="chèque">Chèque</option>
            <option value="espèces">Espèces</option>
            <option value="inconnu">Inconnu</option>
          </select>
        </div>
        <div class="field" style="grid-column:span 3;"><label>Libellé</label><input type="text" name="method_label" placeholder="ex: Virt. SEPA"></div>
        <div style="display:flex;align-items:flex-end;">
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
            <span class="material-icons-round" style="font-size:16px;">save</span> Enregistrer
          </button>
        </div>
      </form>
    </div>

    <!-- KPI summary -->
    <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:12px;margin-bottom:16px;">
      <div class="stat-card accent-green">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:8px;">Total encaissé</div>
        <div style="font-size:28px;font-weight:800;color:#0f172a;line-height:1;"><?= number_format($totalCollected, 0, ',', ' ') ?> €</div>
      </div>
      <?php foreach (array_slice($methodsBreakdown, 0, 3) as $mb): ?>
      <div class="stat-card accent-blue">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:8px;"><?= htmlspecialchars(ucfirst($mb['method']), ENT_QUOTES, 'UTF-8') ?></div>
        <div style="font-size:22px;font-weight:800;color:#0f172a;line-height:1;"><?= number_format((float)$mb['total_amount'], 0, ',', ' ') ?> €</div>
        <div style="font-size:11px;color:#94a3b8;margin-top:6px;"><?= (int)$mb['count'] ?> paiements</div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Charts -->
    <div style="display:grid;grid-template-columns:1fr 2fr;gap:16px;margin-bottom:16px;">
      <div class="panel" style="padding:20px;">
        <div class="panel-head">
          <span class="material-icons-round" style="font-size:16px;color:#3b82f6;">pie_chart</span>
          Répartition par mode
        </div>
        <div style="position:relative;height:200px;">
          <canvas id="methodsChart"></canvas>
        </div>
      </div>
      <div class="panel" style="padding:20px;">
        <div class="panel-head">
          <span class="material-icons-round" style="font-size:16px;color:#10b981;">show_chart</span>
          Encaissements mensuels (12 mois)
        </div>
        <div style="position:relative;height:200px;">
          <canvas id="monthlyChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="panel" style="overflow:hidden;">
      <div style="padding:14px 16px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <div style="font-size:13px;font-weight:600;color:#0f172a;">Derniers paiements</div>
        <a href="<?= APP_URL ?>/export/csv?type=payments" class="btn btn-ghost" style="font-size:12px;padding:5px 10px;">
          <span class="material-icons-round" style="font-size:14px;">download</span> CSV
        </a>
      </div>
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead><tr>
            <th>Date</th>
            <th>Client</th>
            <th>Facture</th>
            <th style="text-align:right;">Montant</th>
            <th>Mode</th>
            <th>Libellé</th>
            <th></th>
          </tr></thead>
          <tbody>
            <?php if (empty($recentPayments)): ?>
            <tr><td colspan="7" style="text-align:center;padding:32px;color:#94a3b8;">Aucun paiement enregistré.</td></tr>
            <?php else: ?>
            <?php foreach ($recentPayments as $p): ?>
            <tr>
              <td style="white-space:nowrap;color:#64748b;font-size:12px;"><?= $p['date_payment'] ? date('d/m/Y', strtotime($p['date_payment'])) : '–' ?></td>
              <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($p['tiers_name'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
              <td style="font-size:12px;color:#64748b;"><?= htmlspecialchars($p['invoice_ref'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
              <td style="text-align:right;font-weight:700;white-space:nowrap;"><?= number_format((float)$p['amount'], 2, ',', ' ') ?> €</td>
              <td><span class="badge <?= $methodCss[$p['method']] ?? 'badge-slate' ?>"><?= htmlspecialchars(ucfirst($p['method'] ?? 'inconnu'), ENT_QUOTES, 'UTF-8') ?></span></td>
              <td style="font-size:11px;color:#94a3b8;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($p['method_label'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <button @click="confirmDelete={id:<?= (int)$p['id'] ?>,amount:'<?= number_format((float)$p['amount'],2,',',' ') ?> €'}" class="btn btn-danger" style="padding:4px 8px;font-size:12px;">
                  <span class="material-icons-round" style="font-size:13px;">delete</span>
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Delete modal -->
    <div v-if="confirmDelete" style="position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:flex;align-items:center;justify-content:center;padding:16px;" @click.self="confirmDelete=null">
      <div style="background:#fff;border-radius:12px;box-shadow:0 25px 50px rgba(0,0,0,.2);width:100%;max-width:380px;padding:24px;" @click.stop>
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
          <div style="width:40px;height:40px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <span class="material-icons-round" style="color:#dc2626;font-size:20px;">delete_forever</span>
          </div>
          <div>
            <div style="font-size:15px;font-weight:700;color:#0f172a;">Supprimer ce paiement ?</div>
            <div style="font-size:13px;color:#64748b;margin-top:2px;">{{ confirmDelete.amount }}</div>
          </div>
        </div>
        <div style="display:flex;gap:8px;">
          <button @click="confirmDelete=null" class="btn btn-ghost" style="flex:1;justify-content:center;">Annuler</button>
          <form :action="'<?= APP_URL ?>/payments/delete/' + confirmDelete.id" method="POST" style="flex:1;">
            <input type="hidden" name="csrf_token" :value="csrf">
            <button type="submit" class="btn btn-primary" style="width:100%;background:#dc2626;justify-content:center;border-color:#dc2626;">Supprimer</button>
          </form>
        </div>
      </div>
    </div>

  </main>
</div>

<?php
$mLabels   = json_encode(array_map('ucfirst', array_column($methodsBreakdown, 'method')));
$mValues   = json_encode(array_column($methodsBreakdown, 'total_amount'));
$mLabels12 = json_encode(array_column($monthlyTotals, 'label'));
$mValues12 = json_encode(array_column($monthlyTotals, 'amount'));
?>
<script>
const { createApp, ref } = Vue;
createApp({
  setup() {
    return {
      confirmDelete: ref(null),
      csrf: '<?= $csrf ?>'
    };
  }
}).mount('#payments-page');

new Chart(document.getElementById('methodsChart'), {
  type: 'doughnut',
  data: { labels: <?= $mLabels ?>, datasets: [{ data: <?= $mValues ?>, backgroundColor: CHART_COLORS, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }] },
  options: { responsive: true, maintainAspectRatio: false, cutout: '60%' }
});
new Chart(document.getElementById('monthlyChart'), {
  type: 'bar',
  data: {
    labels: <?= $mLabels12 ?>,
    datasets: [{ label: 'Encaissements (€)', data: <?= $mValues12 ?>, backgroundColor: 'rgba(16,185,129,.8)', borderColor: '#10b981', borderWidth: 1, borderRadius: 4 }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('fr-FR') + ' €' }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } }
  }
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
