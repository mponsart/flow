<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<?php
$trendIcon  = ['up'=>'trending_up','down'=>'trending_down','stable'=>'trending_flat'][$data['trend']] ?? 'trending_flat';
$trendColor = ['up'=>'text-emerald-600','down'=>'text-red-600','stable'=>'text-slate-500'][$data['trend']] ?? 'text-slate-500';
$healthColor = (int)$data['health'] >= 70 ? 'text-emerald-600' : ((int)$data['health'] >= 40 ? 'text-amber-600' : 'text-red-600');
$healthBg    = (int)$data['health'] >= 70 ? 'bg-emerald-50 border-emerald-200' : ((int)$data['health'] >= 40 ? 'bg-amber-50 border-amber-200' : 'bg-red-50 border-red-200');
$csrf = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<div id="main-wrap" class="flex-1 flex flex-col overflow-hidden ml-64">
  <header class="bg-white border-b border-slate-200 px-6 h-16 flex items-center justify-between flex-shrink-0 sticky top-0 z-20">
    <div class="flex items-center gap-3">
      <button id="menu-toggle" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100">
        <span class="material-icons-round">menu</span>
      </button>
      <span class="material-icons-round text-blue-600 text-2xl">insights</span>
      <h1 class="text-xl font-semibold text-slate-900">Prévisions</h1>
    </div>
    <div class="flex items-center gap-3">
      <?php if (!empty($user['avatar'])): ?>
      <img src="<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>" class="w-9 h-9 rounded-full object-cover">
      <?php endif; ?>
      <span class="text-sm font-medium text-slate-700 hidden sm:block"><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </header>

  <main class="flex-1 overflow-y-auto p-6 space-y-5">

    <?php if (!empty($_GET['message'])): ?>
    <div class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 text-sm">
      <span class="material-icons-round text-emerald-500 text-lg flex-shrink-0 mt-0.5">check_circle</span>
      <?= htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($_GET['error'])): ?>
    <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
      <span class="material-icons-round text-red-500 text-lg flex-shrink-0 mt-0.5">error</span>
      <?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>

    <!-- Error banner -->
    <?php if (!empty($data['error_message'])): ?>
    <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 text-sm">
      <span class="material-icons-round text-amber-500 text-lg flex-shrink-0 mt-0.5">warning_amber</span>
      <?= htmlspecialchars($data['error_message'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>

    <?php if (!$data['expenses_available']): ?>
    <div class="flex items-start gap-3 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl px-4 py-3 text-sm">
      <span class="material-icons-round text-blue-400 text-lg flex-shrink-0 mt-0.5">info</span>
      Aucune dépense enregistrée — les projections nettes ne tiennent pas compte des charges.
    </div>
    <?php endif; ?>

    <!-- KPI grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-4">
      <div class="bg-white border <?= $healthBg ?> rounded-xl p-4 shadow-sm">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Score santé</p>
        <p class="text-3xl font-bold <?= $healthColor ?> mt-1"><?= (int)$data['health'] ?><span class="text-base">/100</span></p>
      </div>
      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm flex items-center gap-3">
        <div>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Tendance</p>
          <p class="text-sm font-bold text-slate-900 mt-1 capitalize"><?= htmlspecialchars($data['trend'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <span class="material-icons-round text-3xl <?= $trendColor ?>"><?= $trendIcon ?></span>
      </div>
      <?php foreach ([
        ['3 mois',  $data['proj3']],
        ['6 mois',  $data['proj6']],
        ['12 mois', $data['proj12']],
      ] as [$label, $proj]): ?>
      <?php
        $lastRev = end($proj['values']);
        $lastNet = end($proj['net_values']);
        reset($proj['values']); reset($proj['net_values']);
      ?>
      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">CA proj. <?= $label ?></p>
        <p class="text-xl font-bold text-slate-900 mt-1"><?= number_format((float)$lastRev, 0, ',', ' ') ?> €</p>
        <p class="text-xs text-slate-500 mt-0.5">Net : <?= number_format((float)$lastNet, 0, ',', ' ') ?> €</p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Main forecast chart -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
      <p class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
        <span class="material-icons-round text-blue-500 text-base">show_chart</span>
        Évolution & Prévisions 12 mois
      </p>
      <div style="position:relative;height:280px;">
        <canvas id="forecastChart"></canvas>
      </div>
    </div>

    <!-- Recurrence setup (client + fréquence uniquement) -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
      <p class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
        <span class="material-icons-round text-emerald-500 text-base">autorenew</span>
        Récurrence de paiement (client + fréquence)
      </p>
      <form method="POST" action="<?= APP_URL ?>/forecast/recurrence/store" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Client *</label>
          <select name="tiers_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">Sélectionner...</option>
            <?php foreach (($tiersAll ?? []) as $t): ?>
            <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Fréquence *</label>
          <select name="period" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="monthly">Mensuelle</option>
            <option value="quarterly">Trimestrielle</option>
            <option value="annual">Annuelle</option>
          </select>
        </div>
        <div class="flex items-end">
          <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <span class="material-icons-round text-base">save</span>
            Enregistrer
          </button>
        </div>
      </form>
      <p class="text-xs text-slate-500 mt-3">Le montant est calculé automatiquement depuis la moyenne des paiements du client.</p>
    </div>

    <!-- Recurring payments configured -->
    <?php if (!empty($data['recurring'])): ?>
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
      <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
        <span class="material-icons-round text-emerald-500 text-base">autorenew</span>
        <p class="text-sm font-semibold text-slate-900">Récurrences enregistrées</p>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50">
            <tr>
              <?php foreach(['Client','Fréquence','Montant moyen','Dernier paiement','Prochaine occurrence',''] as $h): ?>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide whitespace-nowrap"><?= $h ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach ($data['recurring'] as $r): ?>
            <tr class="hover:bg-slate-50 transition-colors">
              <td class="px-4 py-3 text-sm text-slate-700 font-medium"><?= htmlspecialchars($r['tiers_name'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                  <?= htmlspecialchars($r['period_label'] ?? '–', ENT_QUOTES, 'UTF-8') ?>
                </span>
              </td>
              <td class="px-4 py-3 text-sm font-semibold text-slate-900 whitespace-nowrap"><?= number_format((float)$r['amount'], 2, ',', ' ') ?> €</td>
              <td class="px-4 py-3 text-sm text-slate-500 whitespace-nowrap"><?= !empty($r['last_date']) ? date('d/m/Y', strtotime($r['last_date'])) : '–' ?></td>
              <td class="px-4 py-3 text-sm text-blue-600 font-medium whitespace-nowrap"><?= !empty($r['next_date']) ? date('d/m/Y', strtotime($r['next_date'])) : '–' ?></td>
              <td class="px-4 py-3 text-right">
                <form method="POST" action="<?= APP_URL ?>/forecast/recurrence/delete/<?= (int)($r['tiers_id'] ?? 0) ?>">
                  <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                  <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 border border-red-200 text-red-500 text-xs font-medium rounded-lg hover:bg-red-50 transition-colors">
                    <span class="material-icons-round text-xs">delete</span>
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- 12-month detail table -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
      <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
        <span class="material-icons-round text-slate-500 text-base">calendar_month</span>
        <p class="text-sm font-semibold text-slate-900">Détail mois par mois (12 mois)</p>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Mois</th>
              <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">CA projeté</th>
              <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Dépenses</th>
              <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Net</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php
            $proj12 = $data['proj12'];
            foreach ($proj12['labels'] as $i => $label): 
              $rev = $proj12['values'][$i] ?? 0;
              $exp = $proj12['expense_values'][$i] ?? 0;
              $net = $proj12['net_values'][$i] ?? 0;
            ?>
            <tr class="hover:bg-slate-50 transition-colors">
              <td class="px-4 py-3 text-sm font-medium text-slate-900"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></td>
              <td class="px-4 py-3 text-sm text-slate-700 text-right whitespace-nowrap"><?= number_format((float)$rev, 0, ',', ' ') ?> €</td>
              <td class="px-4 py-3 text-sm text-red-500 text-right whitespace-nowrap"><?= number_format((float)$exp, 0, ',', ' ') ?> €</td>
              <td class="px-4 py-3 text-sm font-semibold <?= $net >= 0 ? 'text-emerald-600' : 'text-red-600' ?> text-right whitespace-nowrap">
                <?= number_format((float)$net, 0, ',', ' ') ?> €
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<?php
$hist     = $data['historical'];
$ma3      = $data['ma3'];
$ma6      = $data['ma6'];
$proj12   = $data['proj12'];

$histLabels = json_encode(array_column($hist, 'label'));
$histVals   = json_encode(array_map(fn($h)=>(float)($h['revenue']??0), $hist));
$ma3Vals    = json_encode(array_map(fn($h)=>(float)($h['ma3']??0), $hist));
$ma6Vals    = json_encode(array_map(fn($h)=>(float)($h['ma6']??0), $hist));
$projLabels = json_encode($proj12['labels']);
$projVals   = json_encode($proj12['values']);
$projNet    = json_encode($proj12['net_values']);
$histCount  = count($hist);
?>
<script>
const allLabels = [...<?= $histLabels ?>, ...<?= $projLabels ?>];
const histVals  = <?= $histVals ?>;
const ma3Vals   = <?= $ma3Vals ?>;
const ma6Vals   = <?= $ma6Vals ?>;
const projVals  = <?= $projVals ?>;
const projNet   = <?= $projNet ?>;
const histCount = <?= $histCount ?>;
const nullPad   = (arr, before, after) => [...Array(before).fill(null), ...arr, ...Array(after).fill(null)];

new Chart(document.getElementById('forecastChart'), {
  type: 'line',
  data: {
    labels: allLabels,
    datasets: [
      {
        label: 'Historique CA',
        data: nullPad(histVals, 0, allLabels.length - histCount),
        borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,.1)',
        fill: true, tension: 0.3, borderWidth: 2, pointRadius: 3
      },
      {
        label: 'Moy. 3 mois',
        data: nullPad(ma3Vals, 0, allLabels.length - histCount),
        borderColor: '#f59e0b', fill: false, tension: 0.3,
        borderWidth: 1.5, borderDash: [4,3], pointRadius: 0
      },
      {
        label: 'Moy. 6 mois',
        data: nullPad(ma6Vals, 0, allLabels.length - histCount),
        borderColor: '#8b5cf6', fill: false, tension: 0.3,
        borderWidth: 1.5, borderDash: [6,3], pointRadius: 0
      },
      {
        label: 'Proj. CA',
        data: nullPad(projVals, histCount, 0),
        borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,.08)',
        fill: true, tension: 0.3, borderWidth: 2, borderDash: [5,4], pointRadius: 4
      },
      {
        label: 'Proj. Net',
        data: nullPad(projNet, histCount, 0),
        borderColor: '#ef4444', fill: false, tension: 0.3,
        borderWidth: 1.5, borderDash: [3,3], pointRadius: 3
      },
    ]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
      legend: { labels: { boxWidth: 12, font: { size: 11 } } }
    },
    scales: {
      y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('fr-FR') + ' €' }, grid: { color: '#f1f5f9' } },
      x: { grid: { display: false }, ticks: { maxRotation: 30, font: { size: 10 } } }
    }
  }
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
