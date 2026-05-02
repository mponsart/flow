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

    <!-- KPI Cards annuelles -->
    <div class="kpi-grid">
      <div class="kpi-card">
        <div class="label">CA annuel encaissé</div>
        <div class="value"><?= number_format((float)($annual['annual_revenue'] ?? 0), 0, ',', ' ') ?> €</div>
        <div class="sub">Année <?= (int)($annual['year'] ?? date('Y')) ?></div>
      </div>

      <div class="kpi-card">
        <div class="label">Charges annuelles</div>
        <div class="value" style="color:var(--error);"><?= number_format((float)($annual['annual_expenses'] ?? 0), 0, ',', ' ') ?> €</div>
        <div class="sub">Mensualisé: <?= number_format((float)($annual['monthly_expenses'] ?? 0), 0, ',', ' ') ?> €/mois</div>
      </div>

      <div class="kpi-card <?= ((float)($annual['annual_profit'] ?? 0) >= 0) ? 'success' : 'danger' ?>">
        <div class="label">Résultat annuel</div>
        <?php $profit = (float)($annual['annual_profit'] ?? 0); ?>
        <div class="value"><?= ($profit >= 0 ? '+' : '') . number_format($profit, 0, ',', ' ') ?> €</div>
        <div class="sub"><?= $profit >= 0 ? 'Rentable' : 'Déficitaire' ?></div>
      </div>

      <div class="kpi-card">
        <div class="label">Marge nette</div>
        <?php $margin = (float)($annual['margin_pct'] ?? 0); ?>
        <div class="value" style="color:<?= $margin >= 0 ? 'var(--success)' : 'var(--error)' ?>;"><?= ($margin >= 0 ? '+' : '') . number_format($margin, 1, ',', ' ') ?> %</div>
        <div class="sub">Résultat / CA</div>
      </div>

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
        <span>Impayés: <strong style="color:#202124;"><?= number_format((float)($annual['unpaid_amount'] ?? 0), 0, ',', ' ') ?> €</strong></span>
        <span>Retard: <strong style="color:#202124;"><?= number_format((float)($annual['overdue_amount'] ?? 0), 0, ',', ' ') ?> €</strong></span>
        <span>Panier moyen: <strong style="color:#202124;"><?= number_format((float)($kpis['average_basket'] ?? 0), 0, ',', ' ') ?> €</strong></span>
      </div>
    </div>

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
