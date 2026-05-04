<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<?php
$annual  = $kpis['annual_summary'] ?? [];
$sc = [
  'solide'   => ['text-emerald-700 bg-emerald-100', 'Solide'],
  'fragile'  => ['text-amber-700 bg-amber-100',     'Fragile'],
  'critique' => ['text-red-700 bg-red-100',         'Critique'],
];
function statusBadge(string $s, array $sc): string {
  [$cls, $lbl] = $sc[$s] ?? ['text-slate-600 bg-slate-100', $s];
  return "<span class=\"inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium $cls\">$lbl</span>";
}
$alerts = $annual['alerts'] ?? [];
?>

<!-- Main wrap -->
<div id="main-wrap" class="flex-1 flex flex-col overflow-hidden ml-64">

  <!-- Topbar -->
  <header class="bg-white border-b border-slate-200 px-6 h-16 flex items-center justify-between flex-shrink-0 sticky top-0 z-20">
    <div class="flex items-center gap-3">
      <button id="menu-toggle" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100">
        <span class="material-icons-round">menu</span>
      </button>
      <span class="material-icons-round text-blue-600 text-2xl">dashboard</span>
      <h1 class="text-xl font-semibold text-slate-900 font-display">Tableau de bord</h1>
    </div>
    <div class="flex items-center gap-3">
      <?php if (!empty($user['avatar'])): ?>
      <img src="<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>" class="w-9 h-9 rounded-full object-cover" alt="">
      <?php endif; ?>
      <span class="text-sm font-medium text-slate-700 hidden sm:block"><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </header>

  <!-- Content -->
  <main class="flex-1 overflow-y-auto p-6 space-y-6">

    <section class="relative overflow-hidden rounded-2xl border border-sky-100 bg-gradient-to-br from-sky-50 via-white to-blue-50 px-6 py-5 shadow-sm">
      <div class="absolute -top-16 -right-16 w-56 h-56 rounded-full bg-blue-200/25 blur-2xl"></div>
      <div class="relative flex flex-wrap items-end justify-between gap-4">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[.2em] text-sky-700">Pilotage Financier</p>
          <h2 class="mt-1 text-2xl font-bold text-slate-900 font-display">Vision instantanée <?= (int)($annual['year'] ?? date('Y')) ?></h2>
          <p class="text-sm text-slate-600 mt-1">Synthèse en temps réel de la rentabilité, du cash et du risque client.</p>
        </div>
        <div class="grid grid-cols-2 gap-3 min-w-[260px]">
          <div class="rounded-xl border border-emerald-200 bg-white/80 px-3 py-2">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Résultat mois</p>
            <p class="text-lg font-bold <?= ((float)($annual['monthly_profit']??0))>=0 ? 'text-emerald-600' : 'text-red-600' ?>">
              <?= (((float)($annual['monthly_profit']??0))>=0?'+':'').number_format((float)($annual['monthly_profit']??0),0,',',' ') ?> €
            </p>
          </div>
          <div class="rounded-xl border border-blue-200 bg-white/80 px-3 py-2">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">Run-rate annuel</p>
            <p class="text-lg font-bold text-blue-700"><?= number_format((float)($annual['run_rate_annual']??0),0,',',' ') ?> €</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Alerts -->
    <?php if (!empty($alerts)): ?>
    <div class="space-y-2">
      <?php foreach ($alerts as $alert): ?>
      <div class="flex items-start gap-3 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 text-amber-900 rounded-xl px-4 py-3 text-sm shadow-sm">
        <span class="material-icons-round text-amber-500 text-lg flex-shrink-0 mt-0.5">warning_amber</span>
        <?= htmlspecialchars($alert, ENT_QUOTES, 'UTF-8') ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Executive strip -->
    <div class="bg-white border border-slate-200 rounded-xl px-5 py-3.5 flex flex-wrap items-center gap-3 justify-between shadow-sm">
      <div class="flex items-center gap-2 text-slate-700 font-semibold text-sm">
        <span class="material-icons-round text-blue-600 text-lg">query_stats</span>
        Vue exécutive <?= (int)($annual['year'] ?? date('Y')) ?>
      </div>
      <div class="flex flex-wrap gap-2 text-xs font-semibold text-slate-600">
        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1"><?= statusBadge((string)($annual['margin_status'] ?? 'fragile'), $sc) ?> <span>Marge</span></span>
        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1"><?= statusBadge((string)($annual['collection_status'] ?? 'fragile'), $sc) ?> <span>Encaissement</span></span>
        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1"><?= statusBadge((string)($annual['volatility_status'] ?? 'fragile'), $sc) ?> <span>Volatilité</span></span>
        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1"><?= statusBadge((string)($annual['concentration_status'] ?? 'fragile'), $sc) ?> <span>Concentration</span></span>
        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1"><?= statusBadge((string)($annual['cash_coverage_status'] ?? 'fragile'), $sc) ?> <span>Couverture</span></span>
        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1"><?= statusBadge((string)($annual['delay_risk_status'] ?? 'fragile'), $sc) ?> <span>Retards</span></span>
      </div>
    </div>

    <!-- Section: Performance financière -->
    <div>
      <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Performance financière</p>
      <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
        <?php
        $finKpis = [
          ['CA annuel encaissé', number_format((float)($annual['annual_revenue']??0),0,',',' ').' €', 'Année '.(int)($annual['year']??date('Y')), ''],
          ['Run-rate annuel', number_format((float)($annual['run_rate_annual']??0),0,',',' ').' €', 'Projection rythme actuel', ''],
          ['CA du mois', number_format((float)($annual['monthly_revenue']??0),0,',',' ').' €',
            (((float)($annual['monthly_growth_pct']??0))>=0?'+':'').number_format((float)($annual['monthly_growth_pct']??0),1,',','.').' % vs mois préc.',
            ((float)($annual['monthly_growth_pct']??0))>=0?'text-emerald-600':'text-red-500'],
          ['Résultat annuel',
            (((float)($annual['annual_profit']??0))>=0?'+':'').number_format((float)($annual['annual_profit']??0),0,',',' ').' €',
            ((float)($annual['annual_profit']??0))>=0?'Rentable':'Déficitaire',
            ((float)($annual['annual_profit']??0))>=0?'text-emerald-600':'text-red-500'],
          ['Résultat mensuel',
            (((float)($annual['monthly_profit']??0))>=0?'+':'').number_format((float)($annual['monthly_profit']??0),0,',',' ').' €',
            ((float)($annual['monthly_profit']??0))>=0?'Mois rentable':'Mois déficitaire',
            ((float)($annual['monthly_profit']??0))>=0?'text-emerald-600':'text-red-500'],
          ['Marge nette',
            (((float)($annual['margin_pct']??0))>=0?'+':'').number_format((float)($annual['margin_pct']??0),1,',','.').' %',
            'Résultat / CA',
            ((float)($annual['margin_pct']??0))>=20?'text-emerald-600':(((float)($annual['margin_pct']??0))>=5?'text-amber-600':'text-red-500')],
        ];
        foreach ($finKpis as [$label, $value, $sub, $subColor]): ?>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:-translate-y-0.5 hover:shadow-lg transition-all duration-200">
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide truncate"><?= $label ?></p>
          <p class="text-2xl font-bold text-slate-900 mt-1 leading-none"><?= $value ?></p>
          <p class="text-xs mt-1.5 <?= $subColor ?: 'text-slate-500' ?>"><?= $sub ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Section: Cash & Risque -->
    <div>
      <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Cash & Risque</p>
      <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-4">
        <?php
        $cashKpis = [
          ['Taux de charges', number_format((float)($annual['expense_rate_pct']??0),1,',','.').' %', 'Charges / CA', 'text-amber-600'],
          ['Factures payées', number_format((float)($annual['paid_rate_pct']??0),1,',','.').' %',
            (int)($kpis['invoice_counts']['paid']??0).' / '.(int)($kpis['invoice_counts']['total']??0).' factures', ''],
          ['Taux d\'encaissement', number_format((float)($annual['collection_rate_amount_pct']??0),1,',','.').' %', 'Encaissé / (Encaissé + Impayé)',
            ((float)($annual['collection_rate_amount_pct']??0))>=90?'text-emerald-600':'text-amber-600'],
          ['Concentration Top 3', number_format((float)($annual['top3_share_pct']??0),1,',','.').' %', 'Part CA des 3 plus gros clients',
            ((float)($annual['top3_share_pct']??0))<=40?'text-emerald-600':(((float)($annual['top3_share_pct']??0))<=65?'text-amber-600':'text-red-500')],
          ['Retard moyen', number_format((float)($annual['avg_overdue_days']??0),1,',','.').' j', 'Sur factures en retard',
            ((float)($annual['avg_overdue_days']??0))<=10?'text-emerald-600':(((float)($annual['avg_overdue_days']??0))<=30?'text-amber-600':'text-red-500')],
        ];
        foreach ($cashKpis as [$label, $value, $sub, $subColor]): ?>
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:-translate-y-0.5 hover:shadow-lg transition-all duration-200">
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide truncate"><?= $label ?></p>
          <p class="text-2xl font-bold text-slate-900 mt-1 leading-none"><?= $value ?></p>
          <p class="text-xs mt-1.5 <?= $subColor ?: 'text-slate-500' ?>"><?= $sub ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Charts row -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

      <!-- Revenue evolution -->
      <div class="xl:col-span-2 bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
        <p class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
          <span class="material-icons-round text-blue-500 text-base">show_chart</span>
          Évolution du CA (12 mois)
        </p>
        <div style="position:relative;height:220px;">
          <canvas id="revenueChart"></canvas>
        </div>
      </div>

      <!-- Expense categories -->
      <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
        <p class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
          <span class="material-icons-round text-amber-500 text-base">pie_chart</span>
          Dépenses par catégorie
        </p>
        <?php if (!empty($annual['expense_categories'])): ?>
        <div style="position:relative;height:200px;">
          <canvas id="expenseChart"></canvas>
        </div>
        <?php else: ?>
        <p class="text-sm text-slate-400 text-center py-12">Aucune dépense enregistrée</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Tables row -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

      <!-- Top clients -->
      <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
          <span class="material-icons-round text-blue-500 text-base">star</span>
          <p class="text-sm font-semibold text-slate-900">Top clients (CA)</p>
        </div>
        <table class="w-full">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Client</th>
              <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">CA</th>
              <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Part</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php
            $topTiers  = $kpis['top_tiers'] ?? [];
            $totalRev  = (float)($annual['annual_revenue'] ?? 1);
            foreach (array_slice($topTiers, 0, 8) as $i => $t):
              $share = $totalRev > 0 ? round(((float)$t['revenue'] / $totalRev) * 100, 1) : 0;
            ?>
            <tr class="hover:bg-slate-50 transition-colors">
              <td class="px-4 py-3">
                <div class="flex items-center gap-2.5">
                  <div class="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0"><?= $i+1 ?></div>
                  <a href="<?= APP_URL ?>/tiers/<?= (int)$t['id'] ?>" class="text-sm font-medium text-slate-900 hover:text-blue-600 truncate max-w-[160px]">
                    <?= htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </div>
              </td>
              <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900"><?= number_format((float)$t['revenue'],0,',',' ') ?> €</td>
              <td class="px-4 py-3 text-right">
                <span class="text-xs font-medium text-slate-500"><?= $share ?>%</span>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($topTiers)): ?>
            <tr><td colspan="3" class="px-4 py-6 text-center text-sm text-slate-400">Aucune donnée</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Top services -->
      <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
          <span class="material-icons-round text-emerald-500 text-base">workspace_premium</span>
          <p class="text-sm font-semibold text-slate-900">Top services / produits</p>
        </div>
        <table class="w-full">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Service</th>
              <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">CA</th>
              <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Factures</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach (array_slice($kpis['top_products'] ?? [], 0, 8) as $i => $p): ?>
            <tr class="hover:bg-slate-50 transition-colors">
              <td class="px-4 py-3">
                <div class="flex items-center gap-2.5">
                  <div class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0"><?= $i+1 ?></div>
                  <span class="text-sm font-medium text-slate-900 truncate max-w-[160px]"><?= htmlspecialchars($p['name'] ?? ($p['label'] ?? '–'), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
              </td>
              <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900"><?= number_format((float)($p['revenue']??$p['total']??0),0,',',' ') ?> €</td>
              <td class="px-4 py-3 text-right text-sm text-slate-500"><?= (int)($p['count'] ?? 0) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($kpis['top_products'] ?? [])): ?>
            <tr><td colspan="3" class="px-4 py-6 text-center text-sm text-slate-400">Aucune donnée</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div><!-- end #main-wrap -->

<?php
$revLabels  = json_encode(array_column($kpis['revenue_evolution'] ?? [], 'month'));
$revValues  = json_encode(array_map(fn($r) => (float)($r['revenue']??0), $kpis['revenue_evolution'] ?? []));
$expCats    = $annual['expense_categories'] ?? [];
$expLabels  = json_encode(array_column($expCats, 'category'));
$expValues  = json_encode(array_column($expCats, 'monthly_total'));
?>
<script>
(function(){
  // Revenue line chart
  const rc = document.getElementById('revenueChart');
  if (rc) new Chart(rc, {
    type: 'line',
    data: {
      labels: <?= $revLabels ?>,
      datasets: [{
        label: 'CA (€)',
        data: <?= $revValues ?>,
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59,130,246,.08)',
        borderWidth: 2.5,
        pointBackgroundColor: '#3b82f6',
        pointRadius: 4,
        tension: 0.4,
        fill: true,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('fr-FR') + ' €' }, grid: { color:'#f1f5f9' } },
        x: { grid: { display: false } }
      }
    }
  });

  // Expense donut
  const ec = document.getElementById('expenseChart');
  if (ec) new Chart(ec, {
    type: 'doughnut',
    data: {
      labels: <?= $expLabels ?>,
      datasets: [{ data: <?= $expValues ?>, backgroundColor: CHART_COLORS, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      cutout: '65%',
      plugins: { legend: { position: 'right', labels: { padding: 12, font: { size: 11 } } } }
    }
  });
}());
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
