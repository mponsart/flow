<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<?php
$trendIcon  = ['up'=>'trending_up','down'=>'trending_down','stable'=>'trending_flat'][$data['trend']] ?? 'trending_flat';
$trendColor = ['up'=>'#16a34a','down'=>'#dc2626','stable'=>'#64748b'][$data['trend']] ?? '#64748b';
$healthVal  = (int)$data['health'];
$healthColor = $healthVal >= 70 ? '#16a34a' : ($healthVal >= 40 ? '#d97706' : '#dc2626');
$healthAccent = $healthVal >= 70 ? 'accent-green' : ($healthVal >= 40 ? 'accent-amber' : 'accent-red');
$csrf = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<div id="main-wrap" class="flex-1 flex flex-col overflow-hidden">

  <div class="topbar flex items-center justify-between px-6 h-14 flex-shrink-0 sticky top-0 z-20">
    <div style="display:flex;align-items:center;gap:10px;">
      <button id="menu-toggle" class="lg:hidden" style="background:none;border:none;cursor:pointer;padding:4px;">
        <span class="material-icons-round" style="color:#64748b;font-size:20px;">menu</span>
      </button>
      <span style="font-size:15px;font-weight:700;color:#0f172a;">Prévisions</span>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
      <?php if (!empty($user['avatar'])): ?>
      <img src="<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>" style="width:28px;height:28px;border-radius:50%;" alt="">
      <?php endif; ?>
      <span style="font-size:13px;font-weight:500;color:#475569;"><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </div>

  <main class="flex-1 overflow-y-auto" style="padding:20px 24px;">

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

    <?php if (!empty($data['error_message'])): ?>
    <div class="alert-pill" style="margin-bottom:12px;">
      <span class="material-icons-round" style="font-size:15px;">warning_amber</span>
      <?= htmlspecialchars($data['error_message'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>

    <?php if (!$data['expenses_available']): ?>
    <div style="display:flex;align-items:center;gap:8px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 14px;font-size:13px;color:#1d4ed8;margin-bottom:12px;">
      <span class="material-icons-round" style="font-size:16px;color:#3b82f6;">info</span>
      Aucune dépense enregistrée — les projections nettes ne tiennent pas compte des charges.
    </div>
    <?php endif; ?>

    <!-- KPI grid -->
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:16px;">
      <div class="stat-card <?= $healthAccent ?>">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:8px;">Score santé</div>
        <div style="font-size:28px;font-weight:800;color:<?= $healthColor ?>;line-height:1;"><?= $healthVal ?><span style="font-size:14px;">/100</span></div>
      </div>
      <div class="stat-card accent-blue">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:8px;">Tendance</div>
        <div style="display:flex;align-items:center;gap:6px;">
          <span class="material-icons-round" style="font-size:24px;color:<?= $trendColor ?>;"><?= $trendIcon ?></span>
          <span style="font-size:16px;font-weight:700;color:<?= $trendColor ?>;text-transform:capitalize;"><?= htmlspecialchars($data['trend'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
      </div>
      <?php foreach ([['3 mois', $data['proj3']], ['6 mois', $data['proj6']], ['12 mois', $data['proj12']]] as [$label, $proj]):
        $lastRev = end($proj['values']);
        $lastNet = end($proj['net_values']);
        reset($proj['values']); reset($proj['net_values']);
      ?>
      <div class="stat-card accent-sky">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:8px;">CA proj. <?= $label ?></div>
        <div style="font-size:22px;font-weight:800;color:#0f172a;line-height:1;"><?= number_format((float)$lastRev, 0, ',', ' ') ?> €</div>
        <div style="font-size:11px;color:#64748b;margin-top:6px;">Net : <?= number_format((float)$lastNet, 0, ',', ' ') ?> €</div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Forecast chart -->
    <div class="panel" style="padding:20px;margin-bottom:16px;">
      <div class="panel-head">
        <span class="material-icons-round" style="font-size:16px;color:#3b82f6;">show_chart</span>
        Évolution & Prévisions 12 mois
      </div>
      <div style="position:relative;height:280px;">
        <canvas id="forecastChart"></canvas>
      </div>
    </div>

    <!-- Recurrence config -->
    <div class="panel" style="padding:20px;margin-bottom:16px;">
      <div class="panel-head" style="margin-bottom:16px;">
        <span class="material-icons-round" style="font-size:16px;color:#10b981;">autorenew</span>
        Récurrence de paiement (client + fréquence)
      </div>
      <form method="POST" action="<?= APP_URL ?>/forecast/recurrence/store" style="display:grid;grid-template-columns:1fr 1fr auto;gap:12px;align-items:end;">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="field">
          <label>Client *</label>
          <select name="tiers_id" required>
            <option value="">Sélectionner...</option>
            <?php foreach (($tiersAll ?? []) as $t): ?>
            <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Fréquence *</label>
          <select name="period">
            <option value="monthly">Mensuelle</option>
            <option value="quarterly">Trimestrielle</option>
            <option value="annual">Annuelle</option>
          </select>
        </div>
        <div>
          <button type="submit" class="btn btn-primary">
            <span class="material-icons-round" style="font-size:15px;">save</span> Enregistrer
          </button>
        </div>
      </form>
      <div style="font-size:12px;color:#94a3b8;margin-top:10px;">Le montant est calculé automatiquement depuis la moyenne des paiements du client.</div>
    </div>

    <!-- Recurring table -->
    <?php if (!empty($data['recurring'])): ?>
    <div class="panel" style="overflow:hidden;margin-bottom:16px;">
      <div style="padding:14px 16px;border-bottom:1px solid #f1f5f9;font-size:13px;font-weight:600;color:#0f172a;display:flex;align-items:center;gap:6px;">
        <span class="material-icons-round" style="font-size:15px;color:#10b981;">autorenew</span>
        Récurrences enregistrées
      </div>
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead><tr>
            <th>Client</th>
            <th>Fréquence</th>
            <th style="text-align:right;">Montant moyen</th>
            <th>Dernier paiement</th>
            <th>Prochaine occurrence</th>
            <th></th>
          </tr></thead>
          <tbody>
            <?php foreach ($data['recurring'] as $r): ?>
            <tr>
              <td style="font-weight:600;"><?= htmlspecialchars($r['tiers_name'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
              <td><span class="badge badge-blue"><?= htmlspecialchars($r['period_label'] ?? '–', ENT_QUOTES, 'UTF-8') ?></span></td>
              <td style="text-align:right;font-weight:700;white-space:nowrap;"><?= number_format((float)$r['amount'], 2, ',', ' ') ?> €</td>
              <td style="white-space:nowrap;color:#64748b;font-size:12px;"><?= !empty($r['last_date']) ? date('d/m/Y', strtotime($r['last_date'])) : '–' ?></td>
              <td style="white-space:nowrap;font-weight:600;color:#2563eb;font-size:12px;"><?= !empty($r['next_date']) ? date('d/m/Y', strtotime($r['next_date'])) : '–' ?></td>
              <td style="text-align:right;">
                <form method="POST" action="<?= APP_URL ?>/forecast/recurrence/delete/<?= (int)($r['tiers_id'] ?? 0) ?>">
                  <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                  <button type="submit" class="btn btn-danger" style="padding:5px 10px;font-size:12px;">
                    <span class="material-icons-round" style="font-size:13px;">delete</span>
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

    <!-- Monthly detail table -->
    <div class="panel" style="overflow:hidden;">
      <div style="padding:14px 16px;border-bottom:1px solid #f1f5f9;font-size:13px;font-weight:600;color:#0f172a;display:flex;align-items:center;gap:6px;">
        <span class="material-icons-round" style="font-size:15px;color:#64748b;">calendar_month</span>
        Détail mois par mois (12 mois)
      </div>
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead><tr>
            <th>Mois</th>
            <th style="text-align:right;">CA projeté</th>
            <th style="text-align:right;">Dépenses</th>
            <th style="text-align:right;">Net</th>
          </tr></thead>
          <tbody>
            <?php
            $proj12 = $data['proj12'];
            foreach ($proj12['labels'] as $i => $label):
              $rev = $proj12['values'][$i] ?? 0;
              $exp = $proj12['expense_values'][$i] ?? 0;
              $net = $proj12['net_values'][$i] ?? 0;
            ?>
            <tr>
              <td style="font-weight:600;"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></td>
              <td style="text-align:right;white-space:nowrap;"><?= number_format((float)$rev, 0, ',', ' ') ?> €</td>
              <td style="text-align:right;white-space:nowrap;color:#dc2626;"><?= number_format((float)$exp, 0, ',', ' ') ?> €</td>
              <td style="text-align:right;white-space:nowrap;font-weight:700;color:<?= $net >= 0 ? '#16a34a' : '#dc2626' ?>;"><?= number_format((float)$net, 0, ',', ' ') ?> €</td>
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
      { label: 'Historique CA', data: nullPad(histVals, 0, allLabels.length - histCount), borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,.1)', fill: true, tension: 0.3, borderWidth: 2, pointRadius: 3 },
      { label: 'Moy. 3 mois',   data: nullPad(ma3Vals, 0, allLabels.length - histCount), borderColor: '#f59e0b', fill: false, tension: 0.3, borderWidth: 1.5, borderDash: [4,3], pointRadius: 0 },
      { label: 'Moy. 6 mois',   data: nullPad(ma6Vals, 0, allLabels.length - histCount), borderColor: '#8b5cf6', fill: false, tension: 0.3, borderWidth: 1.5, borderDash: [6,3], pointRadius: 0 },
      { label: 'Proj. CA',      data: nullPad(projVals, histCount, 0), borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,.08)', fill: true, tension: 0.3, borderWidth: 2, borderDash: [5,4], pointRadius: 4 },
      { label: 'Proj. Net',     data: nullPad(projNet, histCount, 0), borderColor: '#ef4444', fill: false, tension: 0.3, borderWidth: 1.5, borderDash: [3,3], pointRadius: 3 },
    ]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: { legend: { labels: { boxWidth: 12, font: { size: 11 } } } },
    scales: {
      y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('fr-FR') + ' €' }, grid: { color: '#f1f5f9' } },
      x: { grid: { display: false }, ticks: { maxRotation: 30, font: { size: 10 } } }
    }
  }
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
