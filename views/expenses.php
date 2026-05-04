<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<?php
$recurrenceLabels = ['monthly'=>'Mensuelle','annual'=>'Annuelle','one_time'=>'Ponctuelle'];
$recurrenceCss    = ['monthly'=>'badge-blue','annual'=>'badge-violet','one_time'=>'badge-slate'];
$csrf = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
$editExpense = $editExpense ?? null;
?>

<div id="main-wrap" class="flex-1 flex flex-col overflow-hidden ml-56">

  <div class="topbar flex items-center justify-between px-6 h-14 flex-shrink-0 sticky top-0 z-20">
    <div style="display:flex;align-items:center;gap:10px;">
      <button id="menu-toggle" class="lg:hidden" style="background:none;border:none;cursor:pointer;padding:4px;">
        <span class="material-icons-round" style="color:#64748b;font-size:20px;">menu</span>
      </button>
      <span style="font-size:15px;font-weight:700;color:#0f172a;">Dépenses</span>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
      <?php if (!empty($user['avatar'])): ?>
      <img src="<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>" style="width:28px;height:28px;border-radius:50%;" alt="">
      <?php endif; ?>
      <span style="font-size:13px;font-weight:500;color:#475569;"><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </div>

  <main class="flex-1 overflow-y-auto" style="padding:20px 24px;" id="expenses-page" v-cloak>

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

    <!-- KPI grid -->
    <?php
    $kpiRows = [
      ['Revenus (mois)',   $revenueMonth,  true,  'trending_up'],
      ['Dépenses (mois)',  $monthlyTotal,  false, 'trending_down'],
      ['Profit (mois)',    $profitMonth,   $profitMonth>=0, 'account_balance'],
      ['Revenus (année)',  $revenueYear,   true,  'calendar_today'],
      ['Dépenses (année)', $annualTotal,   false, 'receipt_long'],
      ['Profit (année)',   $profitYear,    $profitYear>=0, 'savings'],
    ];
    ?>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px;">
      <?php foreach ($kpiRows as [$label,$val,$positive,$icon]):
        $accent = $positive ? 'accent-green' : 'accent-red';
        $color  = $positive ? '#16a34a' : '#dc2626';
      ?>
      <div class="stat-card <?= $accent ?>">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:6px;line-height:1.3;"><?= $label ?></div>
        <div style="font-size:20px;font-weight:800;color:<?= $color ?>;line-height:1;"><?= number_format((float)$val, 0, ',', ' ') ?> €</div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Edit form -->
    <?php if ($editExpense): ?>
    <div class="panel" style="margin-bottom:16px;padding:20px;border-color:#f59e0b;border-width:2px;">
      <div style="font-size:13px;font-weight:600;color:#0f172a;margin-bottom:16px;display:flex;align-items:center;gap:6px;">
        <span class="material-icons-round" style="font-size:16px;color:#d97706;">edit</span>
        Modifier la dépense
      </div>
      <form method="POST" action="<?= APP_URL ?>/expenses/update/<?= (int)$editExpense['id'] ?>" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="field"><label>Libellé *</label><input type="text" name="label" value="<?= htmlspecialchars($editExpense['label'], ENT_QUOTES, 'UTF-8') ?>" required></div>
        <div class="field"><label>Montant (€) *</label><input type="number" name="amount" step="0.01" min="0.01" value="<?= (float)$editExpense['amount'] ?>" required></div>
        <div class="field"><label>Catégorie</label><input type="text" name="category" value="<?= htmlspecialchars($editExpense['category'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="ex: Logiciel…"></div>
        <div class="field">
          <label>Récurrence</label>
          <select name="recurrence">
            <?php foreach ($recurrenceLabels as $k => $v): ?>
            <option value="<?= $k ?>" <?= $editExpense['recurrence']===$k?'selected':'' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Date</label><input type="date" name="expense_date" value="<?= htmlspecialchars($editExpense['expense_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="field" style="grid-column:span 3;"><label>Note</label><input type="text" name="note" value="<?= htmlspecialchars($editExpense['note'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Détail ou commentaire…"></div>
        <div style="grid-column:1/-1;display:flex;gap:8px;padding-top:4px;">
          <button type="submit" class="btn btn-primary"><span class="material-icons-round" style="font-size:15px;">save</span> Enregistrer</button>
          <a href="<?= APP_URL ?>/expenses" class="btn btn-ghost">Annuler</a>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <!-- Toolbar -->
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
      <button @click="showAdd=!showAdd" class="btn btn-primary">
        <span class="material-icons-round" style="font-size:16px;">add</span> Nouvelle dépense
      </button>
      <a href="<?= APP_URL ?>/export/csv?type=expenses" class="btn btn-ghost">
        <span class="material-icons-round" style="font-size:16px;">download</span> CSV
      </a>
    </div>

    <!-- Add form (toggle) -->
    <div v-show="showAdd" class="panel" style="margin-bottom:16px;padding:20px;">
      <div style="font-size:13px;font-weight:600;color:#0f172a;margin-bottom:16px;display:flex;align-items:center;gap:6px;">
        <span class="material-icons-round" style="font-size:16px;color:#2563eb;">add_circle</span>
        Ajouter une dépense
      </div>
      <form method="POST" action="<?= APP_URL ?>/expenses/store" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="field"><label>Libellé *</label><input type="text" name="label" placeholder="ex: Abonnement OVH" required></div>
        <div class="field"><label>Montant (€) *</label><input type="number" name="amount" step="0.01" min="0.01" placeholder="0.00" required></div>
        <div class="field"><label>Catégorie</label><input type="text" name="category" placeholder="ex: Logiciel, Hébergement…"></div>
        <div class="field">
          <label>Récurrence</label>
          <select name="recurrence">
            <option value="monthly">Mensuelle</option>
            <option value="annual">Annuelle</option>
            <option value="one_time">Ponctuelle</option>
          </select>
        </div>
        <div class="field"><label>Date</label><input type="date" name="expense_date"></div>
        <div class="field" style="grid-column:span 3;"><label>Note</label><input type="text" name="note" placeholder="Détail ou commentaire…"></div>
        <div style="grid-column:1/-1;display:flex;gap:8px;padding-top:4px;">
          <button type="submit" class="btn btn-primary"><span class="material-icons-round" style="font-size:15px;">save</span> Ajouter</button>
          <button type="button" @click="showAdd=false" class="btn btn-ghost">Annuler</button>
        </div>
      </form>
    </div>

    <!-- By category -->
    <?php if (!empty($byCategory)): ?>
    <div class="panel" style="padding:16px 20px;margin-bottom:16px;">
      <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:10px;text-transform:uppercase;letter-spacing:.06em;">Répartition par catégorie (ce mois)</div>
      <div style="display:flex;flex-wrap:wrap;gap:8px;">
        <?php foreach ($byCategory as $cat): ?>
        <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;background:#f1f5f9;border-radius:20px;font-size:12px;color:#334155;">
          <?= htmlspecialchars($cat['category'] ?? 'Sans catégorie', ENT_QUOTES, 'UTF-8') ?>
          <strong><?= number_format((float)$cat['monthly_total'], 0, ',', ' ') ?> €</strong>
        </span>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="panel" style="overflow:hidden;">
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead><tr>
            <th>Libellé</th>
            <th style="text-align:right;">Montant</th>
            <th>Catégorie</th>
            <th>Récurrence</th>
            <th>Date</th>
            <th>Note</th>
            <th style="text-align:right;">Actions</th>
          </tr></thead>
          <tbody>
            <?php if (empty($expenses)): ?>
            <tr><td colspan="7" style="text-align:center;padding:32px;color:#94a3b8;">Aucune dépense enregistrée.</td></tr>
            <?php else: ?>
            <?php foreach ($expenses as $exp): ?>
            <tr>
              <td style="font-weight:600;"><?= htmlspecialchars($exp['label'], ENT_QUOTES, 'UTF-8') ?></td>
              <td style="text-align:right;font-weight:700;color:#dc2626;white-space:nowrap;"><?= number_format((float)$exp['amount'], 2, ',', ' ') ?> €</td>
              <td style="color:#64748b;font-size:12px;"><?= htmlspecialchars($exp['category'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
              <td><span class="badge <?= $recurrenceCss[$exp['recurrence']] ?? 'badge-slate' ?>"><?= $recurrenceLabels[$exp['recurrence']] ?? $exp['recurrence'] ?></span></td>
              <td style="white-space:nowrap;color:#64748b;font-size:12px;"><?= !empty($exp['expense_date']) ? date('d/m/Y', strtotime($exp['expense_date'])) : '–' ?></td>
              <td style="font-size:11px;color:#94a3b8;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($exp['note'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              <td style="text-align:right;">
                <div style="display:flex;align-items:center;justify-content:flex-end;gap:4px;">
                  <a href="<?= APP_URL ?>/expenses?edit=<?= (int)$exp['id'] ?>" class="btn btn-ghost" style="padding:5px 10px;font-size:12px;">
                    <span class="material-icons-round" style="font-size:13px;">edit</span>
                  </a>
                  <button @click="confirmDelete={id:<?= (int)$exp['id'] ?>,label:'<?= htmlspecialchars(addslashes($exp['label']), ENT_QUOTES, 'UTF-8') ?>'}" class="btn btn-danger" style="padding:5px 10px;font-size:12px;">
                    <span class="material-icons-round" style="font-size:13px;">delete</span>
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Delete modal -->
    <div v-if="confirmDelete" style="position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:flex;align-items:center;justify-content:center;padding:16px;" @click.self="confirmDelete=null">
      <div style="background:#fff;border-radius:12px;box-shadow:0 25px 50px rgba(0,0,0,.2);width:100%;max-width:380px;padding:24px;" @click.stop>
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
          <div style="width:40px;height:40px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <span class="material-icons-round" style="color:#dc2626;font-size:20px;">delete_forever</span>
          </div>
          <div>
            <div style="font-size:15px;font-weight:700;color:#0f172a;">Supprimer la dépense ?</div>
            <div style="font-size:13px;color:#64748b;margin-top:2px;">{{ confirmDelete.label }}</div>
          </div>
        </div>
        <div style="display:flex;gap:8px;">
          <button @click="confirmDelete=null" class="btn btn-ghost" style="flex:1;justify-content:center;">Annuler</button>
          <form :action="'<?= APP_URL ?>/expenses/delete/' + confirmDelete.id" method="POST" style="flex:1;">
            <input type="hidden" name="csrf_token" :value="csrf">
            <button type="submit" class="btn btn-primary" style="width:100%;background:#dc2626;justify-content:center;border-color:#dc2626;">Supprimer</button>
          </form>
        </div>
      </div>
    </div>

  </main>
</div>

<script>
const { createApp, ref } = Vue;
createApp({
  setup() {
    return {
      showAdd: ref(false),
      confirmDelete: ref(null),
      csrf: '<?= $csrf ?>'
    };
  }
}).mount('#expenses-page');
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
