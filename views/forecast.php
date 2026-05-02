<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<div id="main">
  <header id="topbar">
    <h1><span class="material-icons" style="vertical-align:middle;margin-right:0.5rem;">trending_up</span>Prévisions financières</h1>
    <div class="topbar-user">
      <?php if (!empty($user['avatar'])): ?>
        <img src="<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>" alt="Avatar">
      <?php endif; ?>
      <span><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </header>

  <div id="content">

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
        <div class="value"><?= number_format(array_sum($data['proj3']['values']), 0, ',', ' ') ?> €</div>
        <div class="sub">CA prévu cumulé</div>
      </div>

      <div class="kpi-card">
        <div class="label">Projection 6 mois</div>
        <div class="value"><?= number_format(array_sum($data['proj6']['values']), 0, ',', ' ') ?> €</div>
        <div class="sub">CA prévu cumulé</div>
      </div>

      <div class="kpi-card">
        <div class="label">Projection 12 mois</div>
        <div class="value"><?= number_format(array_sum($data['proj12']['values']), 0, ',', ' ') ?> €</div>
        <div class="sub">CA prévu cumulé</div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Forecast chart -->
    <div class="card" style="margin-bottom:1.5rem;">
      <p class="card-title">
        <span class="material-icons" style="vertical-align:middle;font-size:1rem;">multiline_chart</span>
        Historique + Projections CA (avec moyennes mobiles)
      </p>
      <div class="chart-container" style="height:350px;">
        <canvas id="forecastChart"></canvas>
      </div>
    </div>

    <div class="charts-grid">

      <div class="card" style="grid-column:1/-1;">
        <p class="card-title">
          <span class="material-icons" style="vertical-align:middle;font-size:1rem;">repeat</span>
          Factures récurrentes détectées
        </p>
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
                Aucune récurrence mensuelle, trimestrielle ou annuelle détectée.
              </td>
            </tr>
            <?php else: ?>
            <?php foreach (array_slice($data['recurring'], 0, 12) as $item): ?>
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

      <!-- 3 and 6 month projections -->
      <?php if (!empty($data['proj3']['labels'])): ?>
      <div class="card">
        <p class="card-title">
          <span class="material-icons" style="vertical-align:middle;font-size:1rem;">event</span>
          Projection 3 mois (détail)
        </p>
        <table class="data-table">
          <thead><tr><th>Mois</th><th>CA prévu (€)</th></tr></thead>
          <tbody>
          <?php foreach ($data['proj3']['labels'] as $i => $label): ?>
            <tr>
              <td><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></td>
              <td style="font-weight:500;"><?= number_format($data['proj3']['values'][$i], 0, ',', ' ') ?> €</td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="card">
        <p class="card-title">
          <span class="material-icons" style="vertical-align:middle;font-size:1rem;">date_range</span>
          Projection 12 mois (détail)
        </p>
        <table class="data-table">
          <thead><tr><th>Mois</th><th>CA prévu (€)</th></tr></thead>
          <tbody>
          <?php foreach ($data['proj12']['labels'] as $i => $label): ?>
            <tr>
              <td><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></td>
              <td style="font-weight:500;"><?= number_format($data['proj12']['values'][$i], 0, ',', ' ') ?> €</td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
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

// Nulls for hist dataset in projection range
$histFull = $combinedHist;
$projFull = array_fill(0, count($combinedHist) - 1, null);
$projFull[] = end($combinedHist); // connect last hist point
foreach ($projVals as $v) { $projFull[] = $v; }
$allLabels = array_merge($combinedLabels, $projLabels);

$jHistFull  = json_encode($histFull);
$jProjFull  = json_encode($projFull);
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
