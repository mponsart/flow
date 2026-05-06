<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<?php
$csrf    = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
$editSub = $editSub ?? null;
$recLabels = ['monthly'=>'Mensuel','quarterly'=>'Trimestriel','annual'=>'Annuel','one_time'=>'Unique'];
$recBadge  = ['monthly'=>'badge-blue','quarterly'=>'badge-violet','annual'=>'badge-slate','one_time'=>'badge-amber'];
?>

<div id="main-wrap" class="flex-1 flex flex-col overflow-hidden">

  <div class="topbar flex items-center justify-between px-6 h-14 flex-shrink-0 sticky top-0 z-20">
    <div style="display:flex;align-items:center;gap:10px;">
      <button id="menu-toggle" class="lg:hidden" style="background:none;border:none;cursor:pointer;padding:4px;">
        <span class="material-icons-round" style="color:#64748b;font-size:20px;">menu</span>
      </button>
      <span style="font-size:15px;font-weight:700;color:#0f172a;">Abonnements</span>
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
    <div class="flash-ok" style="margin-bottom:12px;"><span class="material-icons-round" style="font-size:16px;color:#16a34a;">check_circle</span><?= htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
    <div class="flash-err" style="margin-bottom:12px;"><span class="material-icons-round" style="font-size:16px;color:#dc2626;">error</span><?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <!-- KPIs MRR / ARR -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;">
      <div class="stat-card accent-green">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:6px;">MRR (mensuel récurrent)</div>
        <div style="font-size:22px;font-weight:800;color:#16a34a;"><?= number_format($mrr, 0, ',', ' ') ?> €<span style="font-size:12px;font-weight:500;color:#94a3b8;">/mois</span></div>
      </div>
      <div class="stat-card accent-green">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:6px;">ARR (annuel récurrent)</div>
        <div style="font-size:22px;font-weight:800;color:#16a34a;"><?= number_format($arr, 0, ',', ' ') ?> €<span style="font-size:12px;font-weight:500;color:#94a3b8;">/an</span></div>
      </div>
      <div class="stat-card">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:6px;">Abonnements actifs</div>
        <div style="font-size:22px;font-weight:800;color:#0f172a;"><?= (int)array_sum(array_column(array_filter($subscriptions, fn($s) => $s['is_active']), 'is_active')) ?></div>
      </div>
    </div>

    <!-- Edit form -->
    <?php if ($editSub): ?>
    <div class="panel" style="margin-bottom:16px;padding:20px;border-color:#f59e0b;border-width:2px;">
      <div style="font-size:13px;font-weight:600;color:#0f172a;margin-bottom:14px;display:flex;align-items:center;gap:6px;">
        <span class="material-icons-round" style="font-size:16px;color:#d97706;">edit</span>Modifier l'abonnement
      </div>
      <form method="POST" action="<?= APP_URL ?>/subscriptions/update/<?= (int)$editSub['id'] ?>" style="display:grid;grid-template-columns:2fr 2fr 1fr 1fr;gap:12px;">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="field">
          <label>Client *</label>
          <select name="tiers_id" required>
            <?php foreach ($tiers_list as $t): ?>
            <option value="<?= (int)$t['id'] ?>" <?= (int)$t['id']===(int)$editSub['tiers_id']?'selected':'' ?>><?= htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Produit</label>
          <select name="product_id">
            <option value="">– Aucun –</option>
            <?php foreach ($products_list as $p): ?>
            <option value="<?= (int)$p['id'] ?>" <?= (int)$p['id']===(int)$editSub['product_id']?'selected':'' ?>><?= htmlspecialchars($p['label'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Label personnalisé</label><input type="text" name="label" value="<?= htmlspecialchars($editSub['label'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Optionnel"></div>
        <div class="field"><label>Montant (€) *</label><input type="number" name="amount" step="0.01" min="0.01" value="<?= (float)$editSub['amount'] ?>" required></div>
        <div class="field">
          <label>Récurrence</label>
          <select name="recurrence">
            <?php foreach ($recLabels as $k=>$v): ?>
            <option value="<?= $k ?>" <?= $k===$editSub['recurrence']?'selected':'' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Début</label><input type="date" name="start_date" value="<?= htmlspecialchars($editSub['start_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="field"><label>Fin (vide = actif)</label><input type="date" name="end_date" value="<?= htmlspecialchars($editSub['end_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="field" style="display:flex;align-items:flex-end;gap:8px;">
          <label style="display:flex;align-items:center;gap:6px;cursor:pointer;margin-bottom:0;">
            <input type="checkbox" name="is_active" value="1" <?= $editSub['is_active']?'checked':'' ?> style="width:auto;">
            <span style="font-size:12px;font-weight:600;color:#475569;">Actif</span>
          </label>
        </div>
        <div style="grid-column:span 4;display:flex;gap:8px;">
          <button type="submit" class="btn btn-primary"><span class="material-icons-round" style="font-size:14px;">save</span>Enregistrer</button>
          <a href="<?= APP_URL ?>/subscriptions" class="btn btn-ghost">Annuler</a>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <!-- Add form (Vue toggle) -->
    <?php if (!$editSub): ?>
    <div id="add-app" v-cloak>
      <div style="margin-bottom:12px;">
        <button @click="open=!open" class="btn btn-primary">
          <span class="material-icons-round" style="font-size:14px;">add</span>Nouvel abonnement
        </button>
        <a href="<?= APP_URL ?>/products" class="btn btn-ghost" style="margin-left:6px;">
          <span class="material-icons-round" style="font-size:14px;">inventory_2</span>Gérer les produits
        </a>
      </div>
      <div v-show="open" style="margin-bottom:16px;">
        <div class="panel" style="padding:20px;">
          <div class="panel-head" style="margin-bottom:14px;">
            <span class="material-icons-round" style="font-size:16px;color:#2563eb;">autorenew</span>
            Assigner un produit à un client
          </div>
          <form method="POST" action="<?= APP_URL ?>/subscriptions/store" style="display:grid;grid-template-columns:2fr 2fr 1fr 1fr;gap:12px;align-items:end;">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <div class="field">
              <label>Client *</label>
              <select name="tiers_id" required>
                <option value="">Sélectionner…</option>
                <?php foreach ($tiers_list as $t): ?>
                <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label>Produit</label>
              <select name="product_id" id="product-select">
                <option value="">– Aucun –</option>
                <?php foreach ($products_list as $p): ?>
                <option value="<?= (int)$p['id'] ?>" data-price="<?= (float)$p['price'] ?>"><?= htmlspecialchars($p['label'], ENT_QUOTES, 'UTF-8') ?><?= $p['price']>0 ? ' — '.number_format((float)$p['price'],2,',',' ').' €' : '' ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label>Montant (€) *</label>
              <input type="number" name="amount" id="amount-input" step="0.01" min="0.01" required placeholder="0.00">
            </div>
            <div class="field">
              <label>Récurrence</label>
              <select name="recurrence">
                <option value="monthly">Mensuel</option>
                <option value="quarterly">Trimestriel</option>
                <option value="annual">Annuel</option>
                <option value="one_time">Unique</option>
              </select>
            </div>
            <div class="field">
              <label>Label (optionnel)</label>
              <input type="text" name="label" placeholder="ex: Hébergement pro">
            </div>
            <div class="field">
              <label>Date début</label>
              <input type="date" name="start_date">
            </div>
            <div class="field">
              <label>Date fin (vide = illimité)</label>
              <input type="date" name="end_date">
            </div>
            <div style="display:flex;align-items:flex-end;">
              <button type="submit" class="btn btn-primary" style="width:100%;">
                <span class="material-icons-round" style="font-size:14px;">save</span>Créer
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Toolbar -->
    <form method="GET" action="<?= APP_URL ?>/subscriptions" style="display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;">
      <input type="text" name="search" style="flex:1;min-width:160px;padding:7px 11px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;" placeholder="Client ou produit…" value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <select name="recurrence" style="padding:7px 11px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;background:#fff;">
        <option value="">Toutes récurrences</option>
        <?php foreach ($recLabels as $k=>$v): ?>
        <option value="<?= $k ?>" <?= ($recFilter??'')===$k?'selected':'' ?>><?= $v ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-ghost">Filtrer</button>
      <?php if (!empty($search) || !empty($recFilter)): ?><a href="<?= APP_URL ?>/subscriptions" class="btn btn-ghost">Effacer</a><?php endif; ?>
    </form>

    <!-- Table -->
    <div class="panel" style="overflow:hidden;">
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead>
            <tr>
              <th>Client</th>
              <th>Produit / Libellé</th>
              <th>Récurrence</th>
              <th style="text-align:right;">Montant</th>
              <th style="text-align:right;">Equiv. mensuel</th>
              <th>Période</th>
              <th style="text-align:center;">Statut</th>
              <th style="text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($subscriptions)): ?>
            <tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:32px;">Aucun abonnement. Créez-en un ci-dessus.</td></tr>
            <?php else: ?>
            <?php foreach ($subscriptions as $s):
              $equiv = match($s['recurrence']) {
                'monthly'   => (float)$s['amount'],
                'quarterly' => round((float)$s['amount'] / 3, 2),
                'annual'    => round((float)$s['amount'] / 12, 2),
                default     => 0.0,
              };
              $isOver = !empty($s['end_date']) && $s['end_date'] < date('Y-m-d');
              $active = $s['is_active'] && !$isOver;
            ?>
            <tr style="<?= !$active ? 'opacity:.5;' : '' ?>">
              <td style="font-weight:600;"><?= htmlspecialchars($s['tiers_name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td style="color:#334155;">
                <?= htmlspecialchars($s['product_label'] ?: ($s['label'] ?: '–'), ENT_QUOTES, 'UTF-8') ?>
                <?php if (!empty($s['product_ref'])): ?>
                <span style="font-size:11px;color:#94a3b8;margin-left:4px;"><?= htmlspecialchars($s['product_ref'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
              </td>
              <td><span class="badge <?= $recBadge[$s['recurrence']] ?? 'badge-slate' ?>"><?= $recLabels[$s['recurrence']] ?? $s['recurrence'] ?></span></td>
              <td style="text-align:right;font-weight:700;"><?= number_format((float)$s['amount'],2,',',' ') ?> €</td>
              <td style="text-align:right;color:#2563eb;font-weight:600;"><?= $s['recurrence']==='one_time' ? '–' : number_format($equiv,2,',',' ').' €' ?></td>
              <td style="font-size:12px;color:#64748b;">
                <?= !empty($s['start_date']) ? date('d/m/Y',strtotime($s['start_date'])) : '∞' ?>
                <?= !empty($s['end_date']) ? ' → '.date('d/m/Y',strtotime($s['end_date'])) : '' ?>
              </td>
              <td style="text-align:center;">
                <?php if ($isOver): ?>
                <span class="badge badge-red">Expiré</span>
                <?php elseif ($active): ?>
                <span class="badge badge-green">Actif</span>
                <?php else: ?>
                <span class="badge badge-slate">Inactif</span>
                <?php endif; ?>
              </td>
              <td style="text-align:right;">
                <a href="<?= APP_URL ?>/subscriptions?edit=<?= (int)$s['id'] ?>" class="btn btn-ghost" style="padding:4px 8px;font-size:11px;">
                  <span class="material-icons-round" style="font-size:13px;">edit</span>
                </a>
                <form method="POST" action="<?= APP_URL ?>/subscriptions/delete/<?= (int)$s['id'] ?>" style="display:inline;" onsubmit="return confirm('Supprimer cet abonnement ?')">
                  <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                  <button type="submit" class="btn btn-danger" style="padding:4px 8px;font-size:11px;">
                    <span class="material-icons-round" style="font-size:13px;">delete</span>
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php if ($pages > 1): ?>
      <div style="display:flex;align-items:center;justify-content:center;gap:4px;padding:12px;border-top:1px solid #f1f5f9;">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a href="?page=<?= $i ?><?= !empty($search)?'&search='.urlencode($search):'' ?><?= !empty($recFilter)?'&recurrence='.urlencode($recFilter):'' ?>" class="pg-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
    </div>

  </main>
</div>

<script>
Vue.createApp({ data() { return { open: false } } }).mount('#add-app');
// Pré-remplir le montant depuis le prix du produit sélectionné
document.getElementById('product-select')?.addEventListener('change', function() {
  var price = this.options[this.selectedIndex]?.dataset?.price;
  if (price && parseFloat(price) > 0) {
    document.getElementById('amount-input').value = parseFloat(price).toFixed(2);
  }
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
