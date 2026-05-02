<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<div id="main">
  <!-- Top bar -->
  <header id="topbar">
    <h1><span class="material-icons" style="vertical-align:middle;margin-right:0.5rem;">dashboard</span>Tableau de bord</h1>
    <div class="topbar-user">
      <?php if (!empty($user['avatar'])): ?>
        <img src="<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>" alt="Avatar">
      <?php endif; ?>
      <span><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </header>

  <div id="content">

    <?php $annual = $kpis['annual_summary'] ?? []; ?>
    <?php if (empty($annual['expenses_available'])): ?>
      <div style="background:#fff8e1;border:1px solid #ffe08a;color:#8a6d1d;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
        <span class="material-icons" style="font-size:1.1rem;">warning</span>
        Les dépenses ne sont pas encore disponibles. Lance la migration SQL pour activer la rentabilité.
      </div>
    <?php endif; ?>

    <?php
      $statusColor = [
        'solide' => 'var(--success)',
        'fragile' => 'var(--warning)',
        'critique' => 'var(--error)',
      ];
      $marginStatus = $annual['margin_status'] ?? 'fragile';
      $collectionStatus = $annual['collection_status'] ?? 'fragile';
      $volatilityStatus = $annual['volatility_status'] ?? 'fragile';
      $concentrationStatus = $annual['concentration_status'] ?? 'fragile';
      $cashCoverageStatus = $annual['cash_coverage_status'] ?? 'fragile';
      $delayRiskStatus = $annual['delay_risk_status'] ?? 'fragile';
    ?>

    <div class="card" style="margin-bottom:1rem;display:flex;flex-wrap:wrap;gap:0.75rem;align-items:center;justify-content:space-between;">
      <div style="display:flex;align-items:center;gap:0.5rem;">
        <span class="material-icons" style="font-size:1.1rem;color:var(--primary);">query_stats</span>
        <strong>Vue exécutive <?= (int)($annual['year'] ?? date('Y')) ?></strong>
      </div>
      <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
        <span style="background:#e8f0fe;color:#174ea6;padding:0.25rem 0.55rem;border-radius:999px;font-size:0.8rem;">Marge: <?= htmlspecialchars($marginStatus, ENT_QUOTES, 'UTF-8') ?></span>
        <span style="background:#f1f3f4;color:#202124;padding:0.25rem 0.55rem;border-radius:999px;font-size:0.8rem;">Encaissement: <?= htmlspecialchars($collectionStatus, ENT_QUOTES, 'UTF-8') ?></span>
        <span style="background:#fef7e0;color:#8a6d1d;padding:0.25rem 0.55rem;border-radius:999px;font-size:0.8rem;">Volatilité: <?= htmlspecialchars($volatilityStatus, ENT_QUOTES, 'UTF-8') ?></span>
        <span style="background:#fce8e6;color:#b3261e;padding:0.25rem 0.55rem;border-radius:999px;font-size:0.8rem;">Concentration: <?= htmlspecialchars($concentrationStatus, ENT_QUOTES, 'UTF-8') ?></span>
      </div>
    </div>

    <p style="font-size:0.9rem;font-weight:600;color:#5f6368;margin:0.3rem 0 0.8rem;">Performance financière</p>
    <div class="kpi-grid">
      <div class="kpi-card">
        <div class="label">CA annuel encaissé</div>
        <div class="value"><?= number_format((float)($annual['annual_revenue'] ?? 0), 0, ',', ' ') ?> €</div>
        <div class="sub">Année <?= (int)($annual['year'] ?? date('Y')) ?></div>
      </div>
      <div class="kpi-card">
        <div class="label">Run-rate annuel</div>
        <div class="value"><?= number_format((float)($annual['run_rate_annual'] ?? 0), 0, ',', ' ') ?> €</div>
        <div class="sub">Projection à rythme actuel (12 mois)</div>
      </div>
      <div class="kpi-card">
        <div class="label">CA du mois</div>
        <div class="value"><?= number_format((float)($annual['monthly_revenue'] ?? 0), 0, ',', ' ') ?> €</div>
        <div class="sub" style="color:<?= ((float)($annual['monthly_growth_pct'] ?? 0)) >= 0 ? 'var(--success)' : 'var(--error)' ?>;">
          <?= ((float)($annual['monthly_growth_pct'] ?? 0) >= 0 ? '+' : '') . number_format((float)($annual['monthly_growth_pct'] ?? 0), 1, ',', ' ') ?> % vs mois dernier
        </div>
      </div>
      <div class="kpi-card <?= ((float)($annual['annual_profit'] ?? 0) >= 0) ? 'success' : 'danger' ?>">
        <div class="label">Résultat annuel</div>
        <?php $profit = (float)($annual['annual_profit'] ?? 0); ?>
        <div class="value"><?= ($profit >= 0 ? '+' : '') . number_format($profit, 0, ',', ' ') ?> €</div>
        <div class="sub"><?= $profit >= 0 ? 'Rentable' : 'Déficitaire' ?></div>
      </div>
      <div class="kpi-card <?= ((float)($annual['monthly_profit'] ?? 0) >= 0) ? 'success' : 'danger' ?>">
        <div class="label">Résultat mensuel</div>
        <div class="value"><?= ((float)($annual['monthly_profit'] ?? 0) >= 0 ? '+' : '') . number_format((float)($annual['monthly_profit'] ?? 0), 0, ',', ' ') ?> €</div>
        <div class="sub"><?= ((float)($annual['monthly_profit'] ?? 0) >= 0) ? 'Mois rentable' : 'Mois déficitaire' ?></div>
      </div>
      <div class="kpi-card">
        <div class="label">Marge nette</div>
        <?php $margin = (float)($annual['margin_pct'] ?? 0); ?>
        <div class="value" style="color:<?= $statusColor[$marginStatus] ?? 'var(--warning)' ?>;"><?= ($margin >= 0 ? '+' : '') . number_format($margin, 1, ',', ' ') ?> %</div>
        <div class="sub">Résultat / CA</div>
      </div>
    </div>

    <p style="font-size:0.9rem;font-weight:600;color:#5f6368;margin:1rem 0 0.8rem;">Cash et risque</p>
    <div class="kpi-grid">
      <div class="kpi-card warning">
        <div class="label">Taux de charges</div>
        <div class="value"><?= number_format((float)($annual['expense_rate_pct'] ?? 0), 1, ',', ' ') ?> %</div>
        <div class="sub">Charges / CA</div>
      </div>
      <div class="kpi-card">
        <div class="label">Taux de factures payées</div>
        <div class="value"><?= number_format((float)($annual['paid_rate_pct'] ?? 0), 1, ',', ' ') ?> %</div>
        <div class="sub"><?= (int)($kpis['invoice_counts']['paid'] ?? 0) ?> payées / <?= (int)($kpis['invoice_counts']['total'] ?? 0) ?> total</div>
      </div>
      <div class="kpi-card">
        <div class="label">Conversion encaissement</div>
        <div class="value" style="color:<?= $statusColor[$collectionStatus] ?? 'var(--warning)' ?>;"><?= number_format((float)($annual['collection_rate_amount_pct'] ?? 0), 1, ',', ' ') ?> %</div>
        <div class="sub">Encaissé / (encaissé + impayé)</div>
      </div>
      <div class="kpi-card">
        <div class="label">Concentration Top 3</div>
        <div class="value" style="color:<?= $statusColor[$concentrationStatus] ?? 'var(--warning)' ?>;"><?= number_format((float)($annual['top3_share_pct'] ?? 0), 1, ',', ' ') ?> %</div>
        <div class="sub">Part CA des 3 plus gros clients</div>
      </div>
      <div class="kpi-card">
        <div class="label">Couverture charges (mois)</div>
        <div class="value" style="color:<?= $statusColor[$cashCoverageStatus] ?? 'var(--warning)' ?>;">
          <?= $annual['cash_coverage_ratio'] !== null ? number_format((float)$annual['cash_coverage_ratio'], 2, ',', ' ') . 'x' : 'n/a' ?>
        </div>
        <div class="sub">CA mensuel / charges mensuelles</div>
      </div>
      <div class="kpi-card">
        <div class="label">Retard moyen</div>
        <div class="value" style="color:<?= $statusColor[$delayRiskStatus] ?? 'var(--warning)' ?>;"><?= number_format((float)($annual['avg_overdue_days'] ?? 0), 1, ',', ' ') ?> j</div>
        <div class="sub">Sur factures en retard</div>
      </div>
      <div class="kpi-card">
        <div class="label">Clients actifs ce mois</div>
        <div class="value"><?= (int)($annual['active_clients_month'] ?? 0) ?></div>
        <div class="sub">Clients avec paiement sur le mois</div>
      </div>
    </div>

    <div class="card" style="margin-top:1rem;margin-bottom:1.25rem;">
      <p class="card-title">
        <span class="material-icons" style="vertical-align:middle;margin-right:0.25rem;font-size:1rem;">analytics</span>
        Lecture annuelle rapide
      </p>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div>
          <div style="display:flex;justify-content:space-between;font-size:0.875rem;margin-bottom:0.25rem;">
            <span>Poids des charges</span>
            <strong><?= number_format((float)($annual['expense_rate_pct'] ?? 0), 1, ',', ' ') ?> %</strong>
          </div>
          <div style="height:10px;background:#f1f3f4;border-radius:100px;overflow:hidden;">
            <div style="height:100%;width:<?= max(0, min(100, (float)($annual['expense_rate_pct'] ?? 0))) ?>%;background:#f9ab00;"></div>
          </div>
        </div>
        <div>
          <div style="display:flex;justify-content:space-between;font-size:0.875rem;margin-bottom:0.25rem;">
            <span>Factures en retard</span>
            <strong><?= number_format((float)($annual['overdue_rate_pct'] ?? 0), 1, ',', ' ') ?> %</strong>
          </div>
          <div style="height:10px;background:#f1f3f4;border-radius:100px;overflow:hidden;">
            <div style="height:100%;width:<?= max(0, min(100, (float)($annual['overdue_rate_pct'] ?? 0))) ?>%;background:#d93025;"></div>
          </div>
        </div>
      </div>
      <div style="margin-top:0.9rem;color:#5f6368;font-size:0.85rem;display:flex;gap:1.2rem;flex-wrap:wrap;">
        <span>Impayés année en cours: <strong style="color:#202124;"><?= number_format((float)($annual['unpaid_amount_year'] ?? 0), 0, ',', ' ') ?> €</strong></span>
        <span>Retard année en cours: <strong style="color:#202124;"><?= number_format((float)($annual['overdue_amount_year'] ?? 0), 0, ',', ' ') ?> €</strong></span>
        <span>Panier moyen: <strong style="color:#202124;"><?= number_format((float)($kpis['average_basket'] ?? 0), 0, ',', ' ') ?> €</strong></span>
      </div>
    </div>

    <div class="card" style="margin-bottom:1.25rem;">
      <p class="card-title">
        <span class="material-icons" style="vertical-align:middle;margin-right:0.25rem;font-size:1rem;">insights</span>
        Indicateurs de précision
      </p>
      <table class="data-table">
        <thead>
          <tr>
            <th>Indicateur</th>
            <th>Valeur</th>
            <th>Lecture</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Run-rate annuel (12 mois glissants)</td>
            <td style="font-weight:600;"><?= number_format((float)($annual['run_rate_annual'] ?? 0), 0, ',', ' ') ?> €</td>
            <td>Projection annuelle au rythme actuel</td>
          </tr>
          <tr>
            <td>CA mensuel moyen (12 mois)</td>
            <td style="font-weight:600;"><?= number_format((float)($annual['avg_monthly_revenue'] ?? 0), 0, ',', ' ') ?> €</td>
            <td>Niveau moyen d'encaissement par mois</td>
          </tr>
          <tr>
            <td>Volatilité du CA</td>
            <td style="font-weight:600;"><?= number_format((float)($annual['volatility_pct'] ?? 0), 1, ',', ' ') ?> %</td>
            <td style="color:<?= $statusColor[$volatilityStatus] ?? 'var(--warning)' ?>;"><?= ((float)($annual['volatility_pct'] ?? 0) <= 15) ? 'Stable' : ((((float)($annual['volatility_pct'] ?? 0) <= 30) ? 'Modérée' : 'Élevée')) ?></td>
          </tr>
          <tr>
            <td>Concentration client top 1</td>
            <td style="font-weight:600;"><?= number_format((float)($annual['top1_share_pct'] ?? 0), 1, ',', ' ') ?> %</td>
            <td>Part du meilleur client dans le CA annuel</td>
          </tr>
          <tr>
            <td>Concentration client top 3</td>
            <td style="font-weight:600;"><?= number_format((float)($annual['top3_share_pct'] ?? 0), 1, ',', ' ') ?> %</td>
            <td>Risque de dépendance au portefeuille principal</td>
          </tr>
          <tr>
            <td>Part du retard dans les impayés</td>
            <td style="font-weight:600;"><?= number_format((float)($annual['overdue_on_open_pct'] ?? 0), 1, ',', ' ') ?> %</td>
            <td>Priorité de recouvrement sur les dossiers en retard</td>
          </tr>
          <tr>
            <td>Échéances à 15 jours</td>
            <td style="font-weight:600;"><?= number_format((float)($annual['due_soon_amount'] ?? 0), 0, ',', ' ') ?> €</td>
            <td>Montant ouvert à sécuriser à court terme</td>
          </tr>
          <?php if ($annual['runway_months'] !== null): ?>
          <tr>
            <td>Couverture estimée des charges</td>
            <td style="font-weight:600;"><?= number_format((float)$annual['runway_months'], 1, ',', ' ') ?> mois</td>
            <td>Approximation selon CA actuel et charges mensuelles</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if (!empty($annual['alerts'])): ?>
    <div class="card" style="margin-bottom:1.25rem;border-left:4px solid var(--warning);">
      <p class="card-title">
        <span class="material-icons" style="vertical-align:middle;margin-right:0.25rem;font-size:1rem;">notification_important</span>
        Points d'attention prioritaires
      </p>
      <ul style="margin:0;padding-left:1.25rem;color:#444;display:flex;flex-direction:column;gap:0.5rem;">
        <?php foreach ($annual['alerts'] as $alert): ?>
          <li><?= htmlspecialchars((string)$alert, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <!-- Charts -->
    <div class="charts-grid">

      <!-- Revenue evolution -->
      <div class="card" style="grid-column: 1/-1;">
        <p class="card-title">
          <span class="material-icons" style="vertical-align:middle;margin-right:0.25rem;font-size:1rem;">show_chart</span>
          Évolution du chiffre d'affaires encaissé (12 mois)
        </p>
        <div class="chart-container" style="height:300px;">
          <canvas id="revenueChart"></canvas>
        </div>
      </div>

      <!-- Revenue breakdown pie -->
      <div class="card">
        <p class="card-title">
          <span class="material-icons" style="vertical-align:middle;margin-right:0.25rem;font-size:1rem;">pie_chart</span>
          Répartition CA par produit
        </p>
        <div class="chart-container">
          <canvas id="breakdownChart"></canvas>
        </div>
      </div>

      <!-- Top tiers bar -->
      <div class="card">
        <p class="card-title">
          <span class="material-icons" style="vertical-align:middle;margin-right:0.25rem;font-size:1rem;">bar_chart</span>
          Top 10 tiers par CA
        </p>
        <div class="chart-container">
          <canvas id="tiersChart"></canvas>
        </div>
      </div>

      <!-- Top products bar -->
      <div class="card">
        <p class="card-title">
          <span class="material-icons" style="vertical-align:middle;margin-right:0.25rem;font-size:1rem;">inventory_2</span>
          Top 10 produits par CA
        </p>
        <div class="chart-container">
          <canvas id="productsChart"></canvas>
        </div>
      </div>

      <?php if (!empty($annual['expense_categories'])): ?>
      <div class="card" style="grid-column:1/-1;">
        <p class="card-title">
          <span class="material-icons" style="vertical-align:middle;margin-right:0.25rem;font-size:1rem;">account_balance_wallet</span>
          Répartition des charges mensuelles par catégorie
        </p>
        <div style="display:flex;flex-direction:column;gap:0.5rem;">
          <?php
            $maxCat = max($annual['expense_categories']) ?: 1;
            foreach ($annual['expense_categories'] as $cat => $amount):
              $pct = (float)($annual['monthly_expenses'] ?? 0) > 0 ? round(((float)$amount / (float)$annual['monthly_expenses']) * 100) : 0;
          ?>
          <div style="display:flex;align-items:center;gap:1rem;">
            <div style="width:150px;font-size:0.875rem;flex-shrink:0;"><?= htmlspecialchars((string)$cat, ENT_QUOTES, 'UTF-8') ?></div>
            <div style="flex:1;height:18px;background:#f1f3f4;border-radius:6px;overflow:hidden;">
              <div style="height:100%;width:<?= round(((float)$amount / (float)$maxCat) * 100) ?>%;background:#1a73e8;"></div>
            </div>
            <div style="width:130px;text-align:right;font-weight:500;font-size:0.875rem;"><?= number_format((float)$amount, 0, ',', ' ') ?> €/mois</div>
            <div style="width:50px;text-align:right;color:#5f6368;font-size:0.82rem;"><?= $pct ?>%</div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

    </div>

    <!-- Quick actions -->
    <div class="card" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center;">
      <a href="<?= APP_URL ?>/expenses" class="btn btn-outline">
        <span class="material-icons" style="font-size:1rem;">account_balance_wallet</span> Gérer les dépenses
      </a>
      <a href="<?= APP_URL ?>/tiers" class="btn btn-outline">
        <span class="material-icons" style="font-size:1rem;">groups</span> Voir les tiers
      </a>
      <a href="<?= APP_URL ?>/forecast" class="btn btn-outline">
        <span class="material-icons" style="font-size:1rem;">trending_up</span> Prévisions
      </a>
      <a href="<?= APP_URL ?>/export/pdf" target="_blank" class="btn btn-outline">
        <span class="material-icons" style="font-size:1rem;">picture_as_pdf</span> Exporter rapport
      </a>
    </div>

  </div><!-- #content -->
</div><!-- #main -->

<?php
// JSON encode for JS
$revenueLabels   = json_encode(array_column($kpis['revenue_evolution'], 'label'));
$revenueValues   = json_encode(array_column($kpis['revenue_evolution'], 'revenue'));
$breakdownLabels = json_encode(array_column($kpis['revenue_breakdown'], 'label'));
$breakdownValues = json_encode(array_column($kpis['revenue_breakdown'], 'revenue'));
$tiersLabels     = json_encode(array_column($kpis['revenue_by_tiers'], 'name'));
$tiersValues     = json_encode(array_column($kpis['revenue_by_tiers'], 'revenue'));
$productLabels   = json_encode(array_column($kpis['revenue_by_product'], 'label'));
$productValues   = json_encode(array_column($kpis['revenue_by_product'], 'revenue'));
?>

<script>
const COLORS = [
  '#1a73e8','#34a853','#fbbc04','#ea4335','#ab47bc',
  '#00acc1','#ff7043','#66bb6a','#42a5f5','#ec407a'
];

// Revenue evolution
new Chart(document.getElementById('revenueChart'), {
  type: 'line',
  data: {
    labels: <?= $revenueLabels ?>,
    datasets: [{
      label: 'CA (€)',
      data: <?= $revenueValues ?>,
      borderColor: '#1a73e8',
      backgroundColor: 'rgba(26,115,232,0.08)',
      borderWidth: 2,
      fill: true,
      tension: 0.4,
      pointBackgroundColor: '#1a73e8',
      pointRadius: 4,
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { callback: v => v.toLocaleString('fr-FR') + ' €' },
        grid: { color: '#f0f0f0' }
      },
      x: { grid: { display: false } }
    }
  }
});

// Breakdown pie
new Chart(document.getElementById('breakdownChart'), {
  type: 'doughnut',
  data: {
    labels: <?= $breakdownLabels ?>,
    datasets: [{
      data: <?= $breakdownValues ?>,
      backgroundColor: COLORS,
      borderWidth: 2,
      borderColor: '#fff'
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: {
      legend: { position: 'bottom', labels: { padding: 12, font: { size: 12 } } },
      tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + Number(ctx.raw).toLocaleString('fr-FR') + ' €' } }
    }
  }
});

// Top tiers
new Chart(document.getElementById('tiersChart'), {
  type: 'bar',
  data: {
    labels: <?= $tiersLabels ?>,
    datasets: [{
      label: 'CA (€)',
      data: <?= $tiersValues ?>,
      backgroundColor: COLORS,
      borderRadius: 6,
    }]
  },
  options: {
    indexAxis: 'y',
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: {
        beginAtZero: true,
        ticks: { callback: v => v.toLocaleString('fr-FR') + ' €' }
      },
      y: { grid: { display: false } }
    }
  }
});

// Top products
new Chart(document.getElementById('productsChart'), {
  type: 'bar',
  data: {
    labels: <?= $productLabels ?>,
    datasets: [{
      label: 'CA (€)',
      data: <?= $productValues ?>,
      backgroundColor: COLORS.slice(0).reverse(),
      borderRadius: 6,
    }]
  },
  options: {
    indexAxis: 'y',
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: {
        beginAtZero: true,
        ticks: { callback: v => v.toLocaleString('fr-FR') + ' €' }
      },
      y: { grid: { display: false } }
    }
  }
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
