<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<?php
$statusMap = [0=>['Brouillon','bg-slate-100 text-slate-600'],1=>['Validée','bg-amber-100 text-amber-700'],2=>['Payée','bg-emerald-100 text-emerald-700'],3=>['Abandonnée','bg-red-100 text-red-600']];
$methodColors = ['CB'=>'bg-blue-100 text-blue-700','virement'=>'bg-emerald-100 text-emerald-700','chèque'=>'bg-amber-100 text-amber-700','espèces'=>'bg-purple-100 text-purple-700'];
$riskBadge = ['low'=>'bg-emerald-100 text-emerald-700','medium'=>'bg-amber-100 text-amber-700','high'=>'bg-red-100 text-red-700'];
$riskLabel = ['low'=>'Faible','medium'=>'Modéré','high'=>'Élevé'];
?>

<div id="main-wrap" class="flex-1 flex flex-col overflow-hidden">
  <header class="bg-white border-b border-slate-200 px-6 h-16 flex items-center justify-between flex-shrink-0 sticky top-0 z-20">
    <div class="flex items-center gap-3">
      <button id="menu-toggle" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100">
        <span class="material-icons-round">menu</span>
      </button>
      <a href="<?= APP_URL ?>/tiers" class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors">
        <span class="material-icons-round">arrow_back</span>
      </a>
      <span class="material-icons-round text-blue-600 text-2xl">person</span>
      <h1 class="text-xl font-semibold text-slate-900 truncate"><?= htmlspecialchars($tiers['name'], ENT_QUOTES, 'UTF-8') ?></h1>
      <?php if ($tiers['is_active']): ?>
      <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Actif</span>
      <?php else: ?>
      <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Inactif</span>
      <?php endif; ?>
    </div>
    <div class="flex items-center gap-3">
      <?php if (!empty($user['avatar'])): ?>
      <img src="<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>" class="w-9 h-9 rounded-full object-cover">
      <?php endif; ?>
      <span class="text-sm font-medium text-slate-700 hidden sm:block"><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </header>

  <main class="flex-1 overflow-y-auto p-6 space-y-5">

    <!-- Alerts -->
    <?php if (!empty($alerts)): ?>
    <div class="space-y-2">
      <?php foreach ($alerts as $a): ?>
      <?php $alertCls = ['danger'=>'bg-red-50 border-red-200 text-red-700','warning'=>'bg-amber-50 border-amber-200 text-amber-700','info'=>'bg-blue-50 border-blue-200 text-blue-700'][$a['type']] ?? 'bg-slate-50 border-slate-200 text-slate-700'; ?>
      <div class="flex items-start gap-3 <?= $alertCls ?> border rounded-xl px-4 py-3 text-sm">
        <span class="material-icons-round text-lg flex-shrink-0 mt-0.5"><?= $a['type']==='danger'?'error_outline':($a['type']==='warning'?'warning_amber':'info') ?></span>
        <?= htmlspecialchars($a['message'], ENT_QUOTES, 'UTF-8') ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- KPI grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">CA total</p>
        <p class="text-2xl font-bold text-slate-900 mt-1"><?= number_format((float)($tiers['revenue_paid']??0),0,',',' ') ?> €</p>
        <p class="text-xs text-slate-500 mt-1"><?= (int)($tiers['invoice_count']??0) ?> factures</p>
      </div>
      <div class="bg-white border <?= $riskLevel==='high'?'border-red-300':($riskLevel==='medium'?'border-amber-300':'border-emerald-300') ?> rounded-xl p-4 shadow-sm">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Score risque</p>
        <p class="text-2xl font-bold <?= $riskLevel==='high'?'text-red-600':($riskLevel==='medium'?'text-amber-600':'text-emerald-600') ?> mt-1"><?= (int)$riskScore ?>/100</p>
        <p class="text-xs text-slate-500 mt-1"><?= $riskLabel[$riskLevel] ?? $riskLevel ?></p>
      </div>
      <div class="bg-white border <?= (int)($tiers['overdue_count']??0)>0?'border-red-200':'border-slate-200' ?> rounded-xl p-4 shadow-sm">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">En retard</p>
        <p class="text-2xl font-bold <?= (int)($tiers['overdue_count']??0)>0?'text-red-600':'text-emerald-600' ?> mt-1"><?= (int)($tiers['overdue_count']??0) ?></p>
        <p class="text-xs text-slate-500 mt-1">factures</p>
      </div>
      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Mode de paiement</p>
        <p class="text-sm font-bold text-slate-900 mt-1"><?= htmlspecialchars(ucfirst($mainMethod), ENT_QUOTES, 'UTF-8') ?></p>
        <p class="text-xs text-slate-500 mt-1">Fréquence : <?= htmlspecialchars($frequency, ENT_QUOTES, 'UTF-8') ?></p>
      </div>
      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Retards de paiement</p>
        <p class="text-2xl font-bold text-slate-900 mt-1"><?= (int)($delayStats['delayed_count']??0) ?></p>
        <p class="text-xs text-slate-500 mt-1">moy. <?= round((float)($delayStats['avg_delay_days']??0),1) ?> j</p>
      </div>
      <?php if ($tiers['first_invoice_date']): ?>
      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Client depuis</p>
        <p class="text-sm font-bold text-slate-900 mt-1"><?= date('d/m/Y', strtotime($tiers['first_invoice_date'])) ?></p>
        <p class="text-xs text-slate-500 mt-1"><?= date('Y') - date('Y', strtotime($tiers['first_invoice_date'])) ?> an(s)</p>
      </div>
      <?php endif; ?>
    </div>

    <!-- Charts + Info -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
      <div class="xl:col-span-2 bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
        <p class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
          <span class="material-icons-round text-blue-500 text-base">show_chart</span> Évolution du CA (12 mois)
        </p>
        <div style="position:relative;height:200px;">
          <canvas id="revenueChart"></canvas>
        </div>
      </div>
      <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
        <p class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
          <span class="material-icons-round text-slate-500 text-base">contact_page</span> Informations
        </p>
        <dl class="space-y-2.5 text-sm">
          <?php foreach ([
            ['Email', $tiers['email']??'–'],
            ['Téléphone', $tiers['phone']??'–'],
            ['Adresse', $tiers['address']??'–'],
            ['Dernière facture', $tiers['last_invoice_date'] ? date('d/m/Y', strtotime($tiers['last_invoice_date'])) : '–'],
          ] as [$k, $v]): if ($v && $v !== '–'): ?>
          <div class="flex gap-3">
            <dt class="text-slate-400 w-28 flex-shrink-0"><?= $k ?></dt>
            <dd class="text-slate-900 font-medium"><?= htmlspecialchars($v, ENT_QUOTES, 'UTF-8') ?></dd>
          </div>
          <?php endif; endforeach; ?>
        </dl>
      </div>
    </div>

    <!-- Invoices -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
      <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
        <span class="material-icons-round text-blue-500 text-base">receipt_long</span>
        <p class="text-sm font-semibold text-slate-900">Factures récentes</p>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50">
            <tr>
              <?php foreach(['Référence','Date','Échéance','Payé le','Total HT','Statut'] as $h): ?>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap"><?= $h ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php if (empty($invoices)): ?>
            <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-slate-400">Aucune facture.</td></tr>
            <?php else: ?>
            <?php foreach ($invoices as $inv):
              [$slabel,$sbadge] = $statusMap[$inv['status']] ?? ['–','bg-slate-100 text-slate-600'];
            ?>
            <tr class="hover:bg-slate-50 transition-colors">
              <td class="px-4 py-3 text-sm font-semibold text-slate-900"><?= htmlspecialchars($inv['ref'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="px-4 py-3 text-sm text-slate-600 whitespace-nowrap"><?= $inv['date_invoice'] ? date('d/m/Y', strtotime($inv['date_invoice'])) : '–' ?></td>
              <td class="px-4 py-3 text-sm <?= ($inv['is_overdue'] ? 'text-red-600 font-medium' : 'text-slate-600') ?> whitespace-nowrap"><?= $inv['date_due'] ? date('d/m/Y', strtotime($inv['date_due'])) : '–' ?></td>
              <td class="px-4 py-3 text-sm text-slate-600 whitespace-nowrap"><?= $inv['date_paid'] ? date('d/m/Y', strtotime($inv['date_paid'])) : '–' ?></td>
              <td class="px-4 py-3 text-sm font-semibold text-slate-900 whitespace-nowrap"><?= number_format((float)$inv['total_ht'],2,',',' ') ?> €</td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?= $sbadge ?>"><?= $slabel ?></span>
                <?php if ($inv['is_overdue']): ?>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600 ml-1">Retard</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Payments -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
      <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
        <span class="material-icons-round text-emerald-500 text-base">payments</span>
        <p class="text-sm font-semibold text-slate-900">Historique des paiements</p>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50">
            <tr>
              <?php foreach(['Date','Facture','Montant','Mode'] as $h): ?>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide"><?= $h ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php if (empty($payments)): ?>
            <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-slate-400">Aucun paiement.</td></tr>
            <?php else: ?>
            <?php foreach ($payments as $pay): ?>
            <tr class="hover:bg-slate-50 transition-colors">
              <td class="px-4 py-3 text-sm text-slate-600 whitespace-nowrap"><?= $pay['date_payment'] ? date('d/m/Y', strtotime($pay['date_payment'])) : '–' ?></td>
              <td class="px-4 py-3 text-sm text-slate-600"><?= htmlspecialchars($pay['invoice_ref'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
              <td class="px-4 py-3 text-sm font-semibold text-slate-900 whitespace-nowrap"><?= number_format((float)$pay['amount'],2,',',' ') ?> €</td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?= $methodColors[$pay['method']] ?? 'bg-slate-100 text-slate-600' ?>">
                  <?= htmlspecialchars(ucfirst($pay['method'] ?? 'inconnu'), ENT_QUOTES, 'UTF-8') ?>
                </span>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<?php
$revLabels = json_encode(array_column($revenueHist, 'label'));
$revValues = json_encode(array_map(fn($r)=>(float)($r['revenue']??0), $revenueHist));
?>
<script>
new Chart(document.getElementById('revenueChart'), {
  type: 'bar',
  data: {
    labels: <?= $revLabels ?>,
    datasets: [{
      label: 'CA (€)', data: <?= $revValues ?>,
      backgroundColor: 'rgba(59,130,246,.75)', borderColor: '#3b82f6',
      borderWidth: 1, borderRadius: 5,
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('fr-FR') + ' €' }, grid: { color: '#f1f5f9' } },
      x: { grid: { display: false } }
    }
  }
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
