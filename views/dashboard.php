<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<?php
$annual = $kpis['annual_summary'] ?? [];
$year   = (int)($annual['year'] ?? date('Y'));
$alerts = $annual['alerts'] ?? [];
?>

<div id="main-wrap" class="flex-1 flex flex-col overflow-hidden">

  <!-- Topbar -->
  <div class="topbar flex items-center justify-between px-6 h-14 flex-shrink-0 sticky top-0 z-20">
    <div style="display:flex;align-items:center;gap:10px;">
      <button id="menu-toggle" class="lg:hidden" style="background:none;border:none;cursor:pointer;padding:4px;">
        <span class="material-icons-round" style="color:#64748b;font-size:20px;">menu</span>
      </button>
      <span style="font-size:15px;font-weight:700;color:#0f172a;">Tableau de bord</span>
      <span style="font-size:12px;color:#94a3b8;font-weight:500;"><?= $year ?></span>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
      <?php if (!empty($user['avatar'])): ?>
      <img src="<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>" style="width:28px;height:28px;border-radius:50%;" alt="">
      <?php endif; ?>
      <span style="font-size:13px;font-weight:500;color:#475569;"><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </div>

  <main class="flex-1 overflow-y-auto" style="padding:20px 24px;">

    <!-- Alerts -->
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

    <!-- Primary KPIs -->
    <?php
    $ap = (float)($annual['annual_profit'] ?? 0);
    $mp = (float)($annual['margin_pct']    ?? 0);
    $primary = [
      ['CA annuel encaissé',
        number_format((float)($annual['annual_revenue'] ?? 0), 0, ',', ' ').' €',
        'Encaissé '.$year, 'accent-blue'],
      ['Run-rate annuel',
        number_format((float)($annual['run_rate_annual'] ?? 0), 0, ',', ' ').' €',
        'Projection rythme actuel', 'accent-sky'],
      ['Résultat annuel',
        ($ap >= 0 ? '+' : '').number_format($ap, 0, ',', ' ').' €',
        $ap >= 0 ? 'Rentable' : 'Déficitaire',
        $ap >= 0 ? 'accent-green' : 'accent-red'],
      ['Marge nette',
        ($mp >= 0 ? '+' : '').number_format($mp, 1, ',', '.').' %',
        'Résultat / CA encaissé',
        $mp >= 20 ? 'accent-green' : ($mp >= 5 ? 'accent-amber' : 'accent-red')],
    ];
    ?>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;">
      <?php foreach ($primary as [$label, $val, $sub, $accent]): ?>
      <div class="stat-card <?= $accent ?>">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:8px;"><?= $label ?></div>
        <div style="font-size:26px;font-weight:800;color:#0f172a;line-height:1;"><?= $val ?></div>
        <div style="font-size:12px;color:#64748b;margin-top:6px;"><?= htmlspecialchars($sub) ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Secondary KPIs -->
    <?php
    $mr  = (float)($annual['monthly_revenue']    ?? 0);
    $mgp = (float)($annual['monthly_growth_pct'] ?? 0);
    $mp2 = (float)($annual['monthly_profit']     ?? 0);
    $ep  = (float)($annual['expense_rate_pct']   ?? 0);
    $pr  = (float)($annual['paid_rate_pct']      ?? 0);
    $secondary = [
      ['CA du mois',
        number_format($mr, 0, ',', ' ').' €',
        ($mgp >= 0 ? '↑ ' : '↓ ').abs(round($mgp,1)).'% vs mois préc.',
        $mgp >= 0 ? '#16a34a' : '#dc2626'],
      ['Résultat mois',
        ($mp2 >= 0 ? '+' : '').number_format($mp2, 0, ',', ' ').' €',
        $mp2 >= 0 ? 'Mois rentable' : 'Mois déficitaire',
        $mp2 >= 0 ? '#16a34a' : '#dc2626'],
      ['Taux de charges',
        number_format($ep, 1, ',', '.').' %',
        'Charges / CA', '#d97706'],
      ['Factures payées',
        number_format($pr, 1, ',', '.').' %',
        (int)($kpis['invoice_counts']['paid'] ?? 0).'/'.(int)($kpis['invoice_counts']['total'] ?? 0),
        $pr >= 90 ? '#16a34a' : '#d97706'],
      ['Retard moyen',
        number_format((float)($annual['avg_overdue_days'] ?? 0), 1, ',', '.').' j',
        'Sur factures en retard',
        (float)($annual['avg_overdue_days'] ?? 0) <= 10 ? '#16a34a' : '#dc2626'],
    ];
    ?>
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:16px;">
      <?php foreach ($secondary as [$label, $val, $sub, $subColor]): ?>
      <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $label ?></div>
        <div style="font-size:20px;font-weight:800;color:#0f172a;line-height:1;"><?= $val ?></div>
        <div style="font-size:11px;margin-top:5px;font-weight:500;color:<?= $subColor ?>;"><?= htmlspecialchars($sub) ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Charts -->
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
          <div style="height:175px;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#cbd5e1;">
            <span class="material-icons-round" style="font-size:36px;margin-bottom:8px;">receipt_long</span>
            <span style="font-size:13px;">Aucune dépense</span>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Tables -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">

      <div class="panel" style="overflow:hidden;">
        <div class="panel-head">
          <span class="material-icons-round" style="font-size:15px;color:#3b82f6;">star</span>
          Top clients
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
            $totalRev = max(1, (float)($annual['annual_revenue'] ?? 1));
            foreach (array_slice($topTiers, 0, 8) as $i => $t):
              $share = round(((float)$t['revenue'] / $totalRev) * 100, 1);
            ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:8px;">
                  <span style="width:20px;height:20px;border-radius:50%;background:#2563eb;color:#fff;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><?= $i+1 ?></span>
                  <a href="<?= APP_URL ?>/tiers/<?= (int)$t['id'] ?>" style="font-weight:600;color:#0f172a;text-decoration:none;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;">
                    <?= htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8') ?>
                  </a>
                </div>
              </td>
              <td style="text-align:right;font-weight:700;"><?= number_format((float)$t['revenue'], 0, ',', ' ') ?> €</td>
              <td style="text-align:right;color:#94a3b8;"><?= $share ?>%</td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($topTiers)): ?>
            <tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:24px;">Aucune donnée</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="panel" style="overflow:hidden;">
        <div class="panel-head">
          <span class="material-icons-round" style="font-size:15px;color:#10b981;">workspace_premium</span>
          Top services
        </div>
        <table class="data-table">
          <thead><tr>
            <th>Service</th>
            <th style="text-align:right;">CA</th>
            <th style="text-align:right;">Fct.</th>
          </tr></thead>
          <tbody>
            <?php foreach (array_slice($kpis['top_products'] ?? [], 0, 8) as $i => $p): ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:8px;">
                  <span style="width:20px;height:20px;border-radius:50%;background:#10b981;color:#fff;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><?= $i+1 ?></span>
                  <span style="font-weight:600;color:#0f172a;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;"><?= htmlspecialchars($p['name'] ?? ($p['label'] ?? '–'), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
              </td>
              <td style="text-align:right;font-weight:700;"><?= number_format((float)($p['revenue'] ?? $p['total'] ?? 0), 0, ',', ' ') ?> €</td>
              <td style="text-align:right;color:#94a3b8;"><?= (int)($p['count'] ?? 0) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($kpis['top_products'] ?? [])): ?>
            <tr><td colspan="3" style="text-align:center;color:#94a3b8;padding:24px;">Aucune donnée</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
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
    new Chart(rc, {
      type: 'line',
      data: {
        labels: <?= $revLabels ?>,
        datasets: [{
          data: <?= $revValues ?>,
          borderColor: '#2563eb',
          backgroundColor: 'rgba(37,99,235,.06)',
          borderWidth: 2,
          pointBackgroundColor: '#2563eb',
          pointRadius: 3,
          tension: 0.35,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('fr-FR') + ' €', font: { size: 11, family: 'Inter' } }, grid: { color: '#f1f5f9' } },
          x: { grid: { display: false }, ticks: { font: { size: 11, family: 'Inter' } } }
        }
      }
    });
  }

  const ec = document.getElementById('expenseChart');
  if (ec) {
    ec.style.width = '100%';
    const colors = ['#2563eb','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316','#84cc16'];
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
        plugins: { legend: { position: 'right', labels: { padding: 10, font: { size: 11, family: 'Inter' }, boxWidth: 10 } } }
      }
    });
  }
}());
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
