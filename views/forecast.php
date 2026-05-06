<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>
<?php
// ─── Pré-calculs ───────────────────────────────────────────────────────────
$trendIcon  = ['up'=>'trending_up','down'=>'trending_down','stable'=>'trending_flat'][$data['trend']] ?? 'trending_flat';
$trendColor = ['up'=>'#16a34a','down'=>'#dc2626','stable'=>'#64748b'][$data['trend']] ?? '#64748b';
$trendLabel = ['up'=>'Hausse','down'=>'Baisse','stable'=>'Stable'][$data['trend']] ?? 'Stable';

$healthVal    = (int)($data['health'] ?? 0);
$healthColor  = $healthVal >= 70 ? '#16a34a' : ($healthVal >= 40 ? '#d97706' : '#dc2626');
$healthAccent = $healthVal >= 70 ? 'accent-green' : ($healthVal >= 40 ? 'accent-amber' : 'accent-red');

$mrr  = (float)($data['mrr'] ?? 0);
$arr  = (float)($data['arr'] ?? 0);
$p12  = $data['proj12'];
$totalCa  = (float)($p12['total']          ?? array_sum($p12['values'] ?? []));
$totalNet = (float)($p12['total_net']      ?? array_sum($p12['net_values'] ?? []));
$totalExp = (float)($p12['total_expenses'] ?? array_sum($p12['expense_values'] ?? []));

$margeNetPct = $totalCa > 0 ? round($totalNet / $totalCa * 100, 1) : null;
$partSubsPct = $totalCa > 0 ? round($arr / $totalCa * 100, 1) : null;
$coverPct    = $totalExp > 0 ? round($totalCa / $totalExp * 100, 1) : null;

$subs = $data['subscriptions'] ?? [];
$periodLabels = ['monthly'=>'Mensuelle','quarterly'=>'Trimestrielle','annual'=>'Annuelle','one_time'=>'Unique'];
$periodBadge  = ['monthly'=>'badge-green','quarterly'=>'badge-blue','annual'=>'badge-violet','one_time'=>'badge-slate'];

// MRR équivalent par abonnement + prochaine échéance (helper inline)
function subMrr(array $s): float {
    $a = (float)($s['amount'] ?? 0);
    return match((string)($s['recurrence'] ?? 'monthly')) {
        'monthly' => $a, 'quarterly' => round($a / 3, 2), 'annual' => round($a / 12, 2), default => 0.0
    };
}
function nextOcc(string $start, string $period): string {
    if ($period === 'one_time') return date('d/m/Y', strtotime($start));
    $mod = match($period) { 'monthly'=>'+1 month','quarterly'=>'+3 months','annual'=>'+1 year', default=>'+1 month' };
    $now = strtotime('first day of this month');
    $ts  = strtotime($start);
    while ($ts < $now) $ts = strtotime($mod, $ts);
    return date('d/m/Y', $ts);
}
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

    <!-- ── KPI Row 1 : revenus récurrents & projections ───────────────────── -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:12px;">

      <div class="stat-card accent-violet">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:8px;">MRR</div>
        <div style="font-size:28px;font-weight:800;color:#7c3aed;line-height:1;"><?= number_format($mrr, 2, ',', ' ') ?> €</div>
        <div style="font-size:11px;color:#64748b;margin-top:6px;">Revenus récurrents / mois</div>
      </div>

      <div class="stat-card accent-blue">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:8px;">ARR</div>
        <div style="font-size:28px;font-weight:800;color:#2563eb;line-height:1;"><?= number_format($arr, 0, ',', ' ') ?> €</div>
        <div style="font-size:11px;color:#64748b;margin-top:6px;">Annualisé (MRR × 12)</div>
      </div>

      <div class="stat-card accent-sky">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:8px;">CA PROJETÉ 12 MOIS</div>
        <div style="font-size:28px;font-weight:800;color:#0f172a;line-height:1;"><?= number_format($totalCa, 0, ',', ' ') ?> €</div>
        <div style="font-size:11px;color:#64748b;margin-top:6px;">Cumul encaissements projetés</div>
      </div>

      <div class="stat-card <?= $totalNet >= 0 ? 'accent-green' : 'accent-red' ?>">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:8px;">NET PROJETÉ 12 MOIS</div>
        <div style="font-size:28px;font-weight:800;color:<?= $totalNet >= 0 ? '#16a34a' : '#dc2626' ?>;line-height:1;">
          <?= ($totalNet > 0 ? '+' : '') . number_format($totalNet, 0, ',', ' ') ?> €
        </div>
        <div style="font-size:11px;color:#64748b;margin-top:6px;">Après charges estimées</div>
      </div>

    </div>

    <!-- ── KPI Row 2 : indicateurs % ──────────────────────────────────────── -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;">

      <div class="stat-card <?= $healthAccent ?>">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:6px;">Score santé</div>
        <div style="font-size:24px;font-weight:800;color:<?= $healthColor ?>;line-height:1;"><?= $healthVal ?><span style="font-size:13px;font-weight:600;">/100</span></div>
        <div style="font-size:11px;color:#64748b;margin-top:5px;">Tendance & couverture</div>
      </div>

      <div class="stat-card accent-blue">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:6px;">Tendance</div>
        <div style="display:flex;align-items:center;gap:6px;">
          <span class="material-icons-round" style="font-size:22px;color:<?= $trendColor ?>;"><?= $trendIcon ?></span>
          <span style="font-size:16px;font-weight:700;color:<?= $trendColor ?>;"><?= $trendLabel ?></span>
        </div>
        <div style="font-size:11px;color:#64748b;margin-top:5px;">Sur les 3 derniers mois</div>
      </div>

      <div class="stat-card <?= $margeNetPct === null ? '' : ($margeNetPct >= 0 ? 'accent-green' : 'accent-red') ?>">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:6px;">Marge nette</div>
        <div style="font-size:24px;font-weight:800;color:<?= $margeNetPct === null ? '#94a3b8' : ($margeNetPct >= 0 ? '#16a34a' : '#dc2626') ?>;line-height:1;">
          <?= $margeNetPct === null ? '—' : (($margeNetPct > 0 ? '+' : '') . number_format($margeNetPct, 1, ',', ' ') . ' %') ?>
        </div>
        <div style="font-size:11px;color:#64748b;margin-top:5px;">Net / CA projeté 12m</div>
      </div>

      <div class="stat-card <?= $coverPct === null ? '' : ($coverPct >= 100 ? 'accent-green' : 'accent-amber') ?>">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:6px;">Couverture charges</div>
        <div style="font-size:24px;font-weight:800;color:<?= $coverPct === null ? '#94a3b8' : ($coverPct >= 100 ? '#16a34a' : '#d97706') ?>;line-height:1;">
          <?= $coverPct === null ? '—' : number_format($coverPct, 0, ',', ' ') . ' %' ?>
        </div>
        <div style="font-size:11px;color:#64748b;margin-top:5px;">CA / charges sur 12m</div>
      </div>

    </div>

    <!-- ── Graphique ──────────────────────────────────────────────────────── -->
    <div class="panel" style="padding:20px;margin-bottom:16px;">
      <div class="panel-head" style="margin-bottom:16px;">
        <span class="material-icons-round" style="font-size:16px;color:#3b82f6;">show_chart</span>
        Évolution & Prévisions 12 mois
        <?php if ($mrr > 0): ?>
        <span style="margin-left:auto;font-size:11px;font-weight:500;color:#64748b;background:#f1f5f9;padding:3px 8px;border-radius:12px;">
          Projection basée sur <?= count($subs) ?> abonnement<?= count($subs) > 1 ? 's' : '' ?> actif<?= count($subs) > 1 ? 's' : '' ?>
        </span>
        <?php endif; ?>
      </div>
      <div style="position:relative;height:260px;">
        <canvas id="forecastChart"></canvas>
      </div>
    </div>

    <!-- ── Abonnements actifs ─────────────────────────────────────────────── -->
    <?php if (!empty($subs)): ?>
    <div class="panel" style="overflow:hidden;margin-bottom:16px;">
      <div style="padding:14px 16px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:#0f172a;">
        <span class="material-icons-round" style="font-size:15px;color:#7c3aed;">autorenew</span>
        Abonnements actifs
        <span style="margin-left:auto;font-size:11px;font-weight:600;color:#7c3aed;background:#f5f3ff;padding:2px 8px;border-radius:10px;"><?= count($subs) ?></span>
      </div>
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead><tr>
            <th>Client</th>
            <th>Service / Produit</th>
            <th>Fréquence</th>
            <th style="text-align:right;">Montant</th>
            <th style="text-align:right;">MRR équiv.</th>
            <th>Prochaine échéance</th>
          </tr></thead>
          <tbody>
            <?php foreach ($subs as $s):
              $smrr    = subMrr($s);
              $period  = (string)($s['recurrence'] ?? 'monthly');
              $badgeCls = $periodBadge[$period] ?? 'badge-slate';
              $start   = !empty($s['start_date']) ? (string)$s['start_date'] : date('Y-m-d');
              $next    = nextOcc($start, $period);
            ?>
            <tr>
              <td style="font-weight:600;"><?= htmlspecialchars($s['tiers_name'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
              <td style="color:#334155;"><?= htmlspecialchars($s['label'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
              <td><span class="badge <?= $badgeCls ?>"><?= $periodLabels[$period] ?? $period ?></span></td>
              <td style="text-align:right;font-weight:700;white-space:nowrap;"><?= number_format((float)($s['amount'] ?? 0), 2, ',', ' ') ?> €</td>
              <td style="text-align:right;white-space:nowrap;color:#7c3aed;font-weight:600;"><?= $smrr > 0 ? number_format($smrr, 2, ',', ' ').' €' : '—' ?></td>
              <td style="white-space:nowrap;color:#2563eb;font-weight:600;font-size:12px;"><?= $next ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr style="background:#f8fafc;">
              <td colspan="4" style="font-weight:700;padding:10px 12px;color:#0f172a;">Total MRR</td>
              <td style="text-align:right;font-weight:800;color:#7c3aed;white-space:nowrap;padding:10px 12px;"><?= number_format($mrr, 2, ',', ' ') ?> €/mois</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    <?php else: ?>
    <div style="display:flex;align-items:center;gap:10px;background:#faf5ff;border:1px solid #e9d5ff;border-radius:8px;padding:14px 16px;font-size:13px;color:#7c3aed;margin-bottom:16px;">
      <span class="material-icons-round" style="font-size:18px;">autorenew</span>
      <div>
        <div style="font-weight:600;margin-bottom:2px;">Aucun abonnement actif</div>
        <div style="color:#a78bfa;">Ajoutez des abonnements dans <a href="<?= APP_URL ?>/subscriptions" style="color:#7c3aed;font-weight:600;text-decoration:underline;">l'onglet Abonnements</a> pour obtenir des projections précises.</div>
      </div>
    </div>
    <?php endif; ?>

    <!-- ── Détail mensuel ─────────────────────────────────────────────────── -->
    <div class="panel" style="overflow:hidden;">
      <div style="padding:14px 16px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:#0f172a;">
        <span class="material-icons-round" style="font-size:15px;color:#64748b;">calendar_month</span>
        Détail mois par mois — 12 prochains mois
        <span style="margin-left:auto;font-size:11px;color:#64748b;">Charges : <?= number_format((float)($data['expenses_monthly_base'] ?? 0), 0, ',', ' ') ?> €/mois estimés</span>
      </div>
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead><tr>
            <th>Mois</th>
            <th style="text-align:right;">CA projeté</th>
            <th style="text-align:right;">Évol. %</th>
            <th style="text-align:right;">Charges</th>
            <th style="text-align:right;">Net</th>
            <th style="text-align:right;">% Marge</th>
          </tr></thead>
          <tbody>
            <?php
            foreach (($p12['labels'] ?? []) as $i => $label):
              $rev  = (float)($p12['values'][$i]          ?? 0);
              $prev = $i > 0 ? (float)($p12['values'][$i-1] ?? 0) : 0;
              $exp  = (float)($p12['expense_values'][$i]   ?? 0);
              $net  = (float)($p12['net_values'][$i]       ?? 0);
              $deltaPct = ($i > 0 && $prev > 0) ? round(($rev - $prev) / $prev * 100, 1) : null;
              $margePct = $rev > 0 ? round($net / $rev * 100, 1) : null;
              $rowStyle = $i % 2 === 0 ? '' : 'background:#fafafa;';
            ?>
            <tr style="<?= $rowStyle ?>">
              <td style="font-weight:600;white-space:nowrap;"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></td>
              <td style="text-align:right;white-space:nowrap;font-weight:700;"><?= number_format($rev, 0, ',', ' ') ?> €</td>
              <td style="text-align:right;white-space:nowrap;font-weight:600;color:<?= $deltaPct === null ? '#94a3b8' : ($deltaPct >= 0 ? '#16a34a' : '#dc2626') ?>;">
                <?= $deltaPct === null ? '—' : (($deltaPct > 0 ? '+' : '') . number_format($deltaPct, 1, ',', ' ') . ' %') ?>
              </td>
              <td style="text-align:right;white-space:nowrap;color:#dc2626;"><?= number_format($exp, 0, ',', ' ') ?> €</td>
              <td style="text-align:right;white-space:nowrap;font-weight:700;color:<?= $net >= 0 ? '#16a34a' : '#dc2626' ?>;"><?= ($net > 0 ? '+' : '') . number_format($net, 0, ',', ' ') ?> €</td>
              <td style="text-align:right;white-space:nowrap;font-weight:600;color:<?= $margePct === null ? '#94a3b8' : ($margePct >= 0 ? '#16a34a' : '#dc2626') ?>;">
                <?= $margePct === null ? '—' : (($margePct > 0 ? '+' : '') . number_format($margePct, 1, ',', ' ') . ' %') ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr style="background:#f1f5f9;font-weight:700;">
              <td style="padding:10px 12px;">TOTAL 12 mois</td>
              <td style="text-align:right;padding:10px 12px;white-space:nowrap;"><?= number_format($totalCa, 0, ',', ' ') ?> €</td>
              <td></td>
              <td style="text-align:right;padding:10px 12px;white-space:nowrap;color:#dc2626;"><?= number_format($totalExp, 0, ',', ' ') ?> €</td>
              <td style="text-align:right;padding:10px 12px;white-space:nowrap;color:<?= $totalNet >= 0 ? '#16a34a' : '#dc2626' ?>;"><?= ($totalNet > 0 ? '+' : '') . number_format($totalNet, 0, ',', ' ') ?> €</td>
              <td style="text-align:right;padding:10px 12px;white-space:nowrap;color:<?= $margeNetPct === null ? '#94a3b8' : ($margeNetPct >= 0 ? '#16a34a' : '#dc2626') ?>;">
                <?= $margeNetPct === null ? '—' : (($margeNetPct > 0 ? '+' : '') . number_format($margeNetPct, 1, ',', ' ') . ' %') ?>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

  </main>
</div>

<?php
$hist       = $data['historical'];
$histLabels = json_encode(array_column($hist, 'label'));
$histVals   = json_encode(array_map(fn($h) => (float)($h['revenue'] ?? 0), $hist));
$projLabels = json_encode($p12['labels'] ?? []);
$projVals   = json_encode($p12['values'] ?? []);
$projNet    = json_encode($p12['net_values'] ?? []);
$histCount  = count($hist);
?>
<script>
const allLabels = [...<?= $histLabels ?>, ...<?= $projLabels ?>];
const histVals  = <?= $histVals ?>;
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
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59,130,246,.08)',
        fill: true, tension: 0.3, borderWidth: 2, pointRadius: 3
      },
      {
        label: 'Projection CA',
        data: nullPad(projVals, histCount, 0),
        borderColor: '#10b981',
        backgroundColor: 'rgba(16,185,129,.06)',
        fill: true, tension: 0.3, borderWidth: 2.5, borderDash: [5, 4], pointRadius: 4
      },
      {
        label: 'Projection Net',
        data: nullPad(projNet, histCount, 0),
        borderColor: '#ef4444',
        fill: false, tension: 0.3, borderWidth: 1.5, borderDash: [3, 3], pointRadius: 3
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
      legend: { labels: { boxWidth: 12, font: { size: 11 } } },
      tooltip: {
        callbacks: {
          label: ctx => ' ' + ctx.dataset.label + ' : ' + (ctx.raw ?? 0).toLocaleString('fr-FR', { minimumFractionDigits: 0 }) + ' €'
        }
      }
    },
    scales: {
      y: {
        beginAtZero: false,
        ticks: { callback: v => v.toLocaleString('fr-FR') + ' €', font: { size: 11 } },
        grid: { color: '#f1f5f9' }
      },
      x: {
        grid: { display: false },
        ticks: { maxRotation: 30, font: { size: 10 } }
      }
    }
  }
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
