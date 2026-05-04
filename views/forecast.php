<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<div id="main">
  <header id="topbar">
    <div style="display:flex;align-items:center;min-width:0;">
      <button id="menu-toggle" aria-label="Ouvrir le menu">
        <span class="material-icons">menu</span>
      </button>
      <h1><span class="material-icons" style="vertical-align:middle;margin-right:0.5rem;">trending_up</span>Prévisions financières</h1>
    </div>
    <div class="topbar-user">
      <?php if (!empty($user['avatar'])): ?>
        <img src="<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>" alt="Avatar">
      <?php endif; ?>
      <span><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </header>

  <div id="content">

    <?php if (!empty($data['error_message'])): ?>
    <div style="background:#fdecea;border:1px solid #f5c2c7;color:#842029;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
      <span class="material-icons" style="font-size:1.1rem;">error</span>
      <?= htmlspecialchars($data['error_message'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>

    <?php
      $proj3Gross  = array_sum($data['proj3']['values'] ?? []);
      $proj6Gross  = array_sum($data['proj6']['values'] ?? []);
      $proj12Gross = array_sum($data['proj12']['values'] ?? []);
      $proj3Net    = array_sum($data['proj3']['net_values'] ?? []);
      $proj6Net    = array_sum($data['proj6']['net_values'] ?? []);
      $proj12Net   = array_sum($data['proj12']['net_values'] ?? []);
    ?>

    <?php if (!empty($data['expenses_available'])): ?>
    <div style="background:#e8f0fe;border:1px solid #c6dafc;color:#174ea6;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
      <span class="material-icons" style="font-size:1.1rem;">filter_alt</span>
      Prévision récurrente filtrée: seuls les clients avec facture ce mois-ci sont pris en compte.
    </div>
    <?php endif; ?>

    <!-- KPIs -->
    <div class="kpi-grid" style="margin-bottom:2rem;">
      <div class="kpi-card">
        <div class="label">Tendance</div>
        <div class="value" style="font-size:1.5rem;">
          <?php
            $trendIcon  = ['up' => '▲', 'down' => '▼', 'stable' => '→'][$data['trend']] ?? '→';
            $trendColor = ['up' => 'var(--success)', 'down' => 'var(--error)', 'stable' => 'var(--warning)'][$data['trend']] ?? '#333';
            $trendLabel = ['up' => 'Hausse', 'down' => 'Baisse', 'stable' => 'Stable'][$data['trend']] ?? '–';
          ?>
          <span style="color:<?= $trendColor ?>"><?= $trendIcon ?> <?= $trendLabel ?></span>
        </div>
        <div class="sub">sur les 3 derniers mois</div>
      </div>

      <div class="kpi-card">
        <div class="label">Score de santé financière</div>
        <div class="value"><?= $data['health'] ?>/100</div>
        <div class="sub">
          <?php
            $h = $data['health'];
            if ($h >= 70) echo '<span style="color:var(--success)">Bonne santé</span>';
            elseif ($h >= 40) echo '<span style="color:var(--warning)">À surveiller</span>';
            else echo '<span style="color:var(--error)">Critique</span>';
          ?>
        </div>
      </div>

      <?php if (!empty($data['proj3']['values'])): ?>
      <div class="kpi-card">
        <div class="label">Projection 3 mois</div>
        <div class="value"><?= number_format($proj3Gross, 0, ',', ' ') ?> €</div>
        <div class="sub" style="display:flex;justify-content:space-between;gap:1rem;">
          <span>CA brut</span>
          <span style="color:<?= $proj3Net >= 0 ? 'var(--success)' : 'var(--error)' ?>;font-weight:500;">Net: <?= ($proj3Net >= 0 ? '+' : '') . number_format($proj3Net, 0, ',', ' ') ?> €</span>
        </div>
      </div>

      <div class="kpi-card">
        <div class="label">Projection 6 mois</div>
        <div class="value"><?= number_format($proj6Gross, 0, ',', ' ') ?> €</div>
        <div class="sub" style="display:flex;justify-content:space-between;gap:1rem;">
          <span>CA brut</span>
          <span style="color:<?= $proj6Net >= 0 ? 'var(--success)' : 'var(--error)' ?>;font-weight:500;">Net: <?= ($proj6Net >= 0 ? '+' : '') . number_format($proj6Net, 0, ',', ' ') ?> €</span>
        </div>
      </div>

      <div class="kpi-card">
        <div class="label">Projection 12 mois</div>
        <div class="value"><?= number_format($proj12Gross, 0, ',', ' ') ?> €</div>
        <div class="sub" style="display:flex;justify-content:space-between;gap:1rem;">
          <span>CA brut</span>
          <span style="color:<?= $proj12Net >= 0 ? 'var(--success)' : 'var(--error)' ?>;font-weight:500;">Net: <?= ($proj12Net >= 0 ? '+' : '') . number_format($proj12Net, 0, ',', ' ') ?> €</span>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Forecast chart -->
    <div class="card" style="margin-bottom:1.5rem;">
      <p class="card-title">
        <span class="material-icons" style="vertical-align:middle;font-size:1rem;">multiline_chart</span>
        Historique + Projections (CA brut / Charges / Net)
      </p>
      <div class="chart-container" style="height:350px;">
        <canvas id="forecastChart"></canvas>
      </div>
    </div>

    <div class="charts-grid">

      <div class="card" style="grid-column:1/-1;">
        <p class="card-title">
          <span class="material-icons" style="vertical-align:middle;font-size:1rem;">repeat</span>
          Échéances prévisionnelles détectées
        </p>
        <div class="table-scroll">
        <table class="data-table">
          <thead>
            <tr>
              <th>Tiers</th>
              <th>Service</th>
              <th>Fréquence</th>
              <th>Montant moyen</th>
              <th>Dernière facture</th>
              <th>Prochaine échéance</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($data['recurring'])): ?>
            <tr>
              <td colspan="6" style="text-align:center;color:#5f6368;padding:2rem;">
                Aucune facture payée disponible pour calculer les échéances.
              </td>
            </tr>
            <?php else: ?>
            <?php foreach ($data['recurring'] as $item): ?>
            <tr>
              <td><?= htmlspecialchars($item['tiers_name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($item['service_label'], ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <span class="badge badge-info">
                  <?= htmlspecialchars($item['period_label'], ENT_QUOTES, 'UTF-8') ?>
                </span>
              </td>
              <td style="font-weight:500;"><?= number_format((float)$item['amount'], 0, ',', ' ') ?> €</td>
              <td><?= htmlspecialchars(date('d/m/Y', strtotime($item['last_date'])), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars(date('d/m/Y', strtotime($item['next_date'])), ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
        </div>
      </div>

      <!-- 3 and 6 month projections -->
      <?php if (!empty($data['proj3']['labels'])): ?>
      <div class="card">
        <p class="card-title">
          <span class="material-icons" style="vertical-align:middle;font-size:1rem;">event</span>
          Projection 3 mois (détail)
        </p>
        <div class="table-scroll">
        <table class="data-table">
          <thead><tr><th>Mois</th><th>CA brut (€)</th><th>Charges (€)</th><th>Net (€)</th></tr></thead>
          <tbody>
          <?php foreach ($data['proj3']['labels'] as $i => $label): ?>
            <tr>
              <td><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></td>
              <td style="font-weight:500;"><?= number_format($data['proj3']['values'][$i], 0, ',', ' ') ?> €</td>
              <td><?= number_format($data['proj3']['expense_values'][$i] ?? 0, 0, ',', ' ') ?> €</td>
              <td style="font-weight:500;color:<?= (($data['proj3']['net_values'][$i] ?? 0) >= 0) ? 'var(--success)' : 'var(--error)' ?>;">
                <?= (($data['proj3']['net_values'][$i] ?? 0) >= 0 ? '+' : '') . number_format($data['proj3']['net_values'][$i] ?? 0, 0, ',', ' ') ?> €
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      </div>

      <div class="card">
        <p class="card-title">
          <span class="material-icons" style="vertical-align:middle;font-size:1rem;">date_range</span>
          Projection 12 mois (détail)
        </p>
        <div class="table-scroll">
        <table class="data-table">
          <thead><tr><th>Mois</th><th>CA brut (€)</th><th>Charges (€)</th><th>Net (€)</th></tr></thead>
          <tbody>
          <?php foreach ($data['proj12']['labels'] as $i => $label): ?>
            <tr>
              <td><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></td>
              <td style="font-weight:500;"><?= number_format($data['proj12']['values'][$i], 0, ',', ' ') ?> €</td>
              <td><?= number_format($data['proj12']['expense_values'][$i] ?? 0, 0, ',', ' ') ?> €</td>
              <td style="font-weight:500;color:<?= (($data['proj12']['net_values'][$i] ?? 0) >= 0) ? 'var(--success)' : 'var(--error)' ?>;">
                <?= (($data['proj12']['net_values'][$i] ?? 0) >= 0 ? '+' : '') . number_format($data['proj12']['net_values'][$i] ?? 0, 0, ',', ' ') ?> €
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      </div>
      <?php endif; ?>

    </div>

  </div>
</div>

<?php
$histLabels = json_encode(array_column($data['historical'], 'label'));
$histValues = json_encode(array_column($data['historical'], 'revenue'));
$ma3Values  = json_encode($data['ma3']);
$ma6Values  = json_encode($data['ma6']);

// Build combined labels/values: historical + projection 12m
$combinedLabels = array_column($data['historical'], 'label');
$combinedHist   = array_column($data['historical'], 'revenue');
$projLabels     = $data['proj12']['labels'] ?? [];
$projVals       = $data['proj12']['values'] ?? [];
$projExpenseVals = $data['proj12']['expense_values'] ?? [];
$projNetVals    = $data['proj12']['net_values'] ?? [];
$histExpenseVals = $data['historical_expenses'] ?? array_fill(0, count($combinedHist), 0.0);
$histNetVals     = [];
foreach ($combinedHist as $i => $grossVal) {
  $histNetVals[] = (float)$grossVal - (float)($histExpenseVals[$i] ?? 0);
}

// Nulls for hist dataset in projection range
$histFull = $combinedHist;
$projFull = array_fill(0, count($combinedHist) - 1, null);
$projFull[] = end($combinedHist); // connect last hist point
foreach ($projVals as $v) { $projFull[] = $v; }

$expenseHistFull = array_map(static fn($v): float => round((float)$v, 2), $histExpenseVals);
$expenseProjFull = array_fill(0, count($combinedHist) - 1, null);
$expenseProjFull[] = end($expenseHistFull) ?: 0;
foreach ($projExpenseVals as $v) { $expenseProjFull[] = $v; }

$netHistFull = array_map(static fn($v): float => round((float)$v, 2), $histNetVals);
$netProjFull = array_fill(0, count($combinedHist) - 1, null);
$netProjFull[] = end($netHistFull) ?: 0;
foreach ($projNetVals as $v) { $netProjFull[] = $v; }

$allLabels = array_merge($combinedLabels, $projLabels);

$jHistFull  = json_encode($histFull);
$jProjFull  = json_encode($projFull);
$jExpenseFull = json_encode($expenseProjFull);
$jNetFull = json_encode($netProjFull);
$jAllLabels = json_encode($allLabels);
$jMa3       = json_encode(array_merge($data['ma3'], array_fill(0, count($projLabels), null)));
$jMa6       = json_encode(array_merge($data['ma6'], array_fill(0, count($projLabels), null)));
?>

<script>
new Chart(document.getElementById('forecastChart'), {
  type: 'line',
  data: {
    labels: <?= $jAllLabels ?>,
    datasets: [
      {
        label: 'CA réel (€)',
        data: <?= $jHistFull ?>,
        borderColor: '#1a73e8',
        backgroundColor: 'rgba(26,115,232,0.07)',
        fill: true,
        tension: 0.3,
        pointRadius: 3,
        borderWidth: 2,
      },
      {
        label: 'Projection (€)',
        data: <?= $jProjFull ?>,
        borderColor: '#34a853',
        backgroundColor: 'rgba(52,168,83,0.07)',
        fill: false,
        borderDash: [6, 4],
        tension: 0.3,
        pointRadius: 3,
        borderWidth: 2,
      },
      {
        label: 'Charges projetées (€)',
        data: <?= $jExpenseFull ?>,
        borderColor: '#f9ab00',
        backgroundColor: 'rgba(249,171,0,0.05)',
        fill: false,
        borderDash: [4, 4],
        tension: 0.25,
        pointRadius: 2,
        borderWidth: 2,
      },
      {
        label: 'Net projeté (€)',
        data: <?= $jNetFull ?>,
        borderColor: '#d93025',
        backgroundColor: 'rgba(217,48,37,0.04)',
        fill: false,
        tension: 0.25,
        pointRadius: 2,
        borderWidth: 2,
      },
      {
        label: 'MM 3 mois',
        data: <?= $jMa3 ?>,
        borderColor: '#fbbc04',
        fill: false,
        borderDash: [3, 3],
        tension: 0.3,
        pointRadius: 0,
        borderWidth: 1.5,
      },
      {
        label: 'MM 6 mois',
        data: <?= $jMa6 ?>,
        borderColor: '#ea4335',
        fill: false,
        borderDash: [3, 3],
        tension: 0.3,
        pointRadius: 0,
        borderWidth: 1.5,
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
      legend: { position: 'top' },
      tooltip: {
        callbacks: {
          label: ctx => ' ' + ctx.dataset.label + ': ' + (ctx.raw !== null ? Number(ctx.raw).toLocaleString('fr-FR') + ' €' : '–')
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { callback: v => v.toLocaleString('fr-FR') + ' €' },
        grid: { color: '#f0f0f0' }
      },
      x: { grid: { display: false }, ticks: { maxRotation: 45 } }
    }
  }
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
