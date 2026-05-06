<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<?php
$csrf        = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
$editProduct = $editProduct ?? null;
$typeLabel   = [0 => 'Produit', 1 => 'Service'];
?>

<div id="main-wrap" class="flex-1 flex flex-col overflow-hidden">

  <div class="topbar flex items-center justify-between px-6 h-14 flex-shrink-0 sticky top-0 z-20">
    <div style="display:flex;align-items:center;gap:10px;">
      <button id="menu-toggle" class="lg:hidden" style="background:none;border:none;cursor:pointer;padding:4px;">
        <span class="material-icons-round" style="color:#64748b;font-size:20px;">menu</span>
      </button>
      <span style="font-size:15px;font-weight:700;color:#0f172a;">Produits &amp; Services</span>
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

    <!-- Edit form -->
    <?php if ($editProduct): ?>
    <div class="panel" style="margin-bottom:16px;padding:20px;border-color:#f59e0b;border-width:2px;">
      <div style="font-size:13px;font-weight:600;color:#0f172a;margin-bottom:14px;display:flex;align-items:center;gap:6px;">
        <span class="material-icons-round" style="font-size:16px;color:#d97706;">edit</span>Modifier le produit
      </div>
      <form method="POST" action="<?= APP_URL ?>/products/update/<?= (int)$editProduct['id'] ?>" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="field"><label>Référence</label><input type="text" name="ref" value="<?= htmlspecialchars($editProduct['ref'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="field"><label>Libellé *</label><input type="text" name="label" value="<?= htmlspecialchars($editProduct['label'], ENT_QUOTES, 'UTF-8') ?>" required></div>
        <div class="field"><label>Prix HT (€)</label><input type="number" name="price" step="0.01" min="0" value="<?= (float)$editProduct['price'] ?>"></div>
        <div class="field"><label>Type</label>
          <select name="type">
            <option value="0" <?= (int)$editProduct['type']===0?'selected':'' ?>>Produit</option>
            <option value="1" <?= (int)$editProduct['type']===1?'selected':'' ?>>Service</option>
          </select>
        </div>
        <div style="grid-column:span 4;display:flex;gap:8px;">
          <button type="submit" class="btn btn-primary"><span class="material-icons-round" style="font-size:14px;">save</span>Enregistrer</button>
          <a href="<?= APP_URL ?>/products" class="btn btn-ghost">Annuler</a>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <!-- Add form (Vue toggle) -->
    <?php if (!$editProduct): ?>
    <div id="add-app" v-cloak>
      <div style="margin-bottom:12px;">
        <button @click="open=!open" class="btn btn-primary">
          <span class="material-icons-round" style="font-size:14px;">add</span>Nouveau produit
        </button>
      </div>
      <div v-show="open" style="margin-bottom:16px;">
        <div class="panel" style="padding:20px;">
          <div class="panel-head" style="margin-bottom:14px;">
            <span class="material-icons-round" style="font-size:16px;color:#2563eb;">inventory_2</span>Nouveau produit / service
          </div>
          <form method="POST" action="<?= APP_URL ?>/products/store" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <div class="field"><label>Référence</label><input type="text" name="ref" placeholder="ex: SVC-001"></div>
            <div class="field"><label>Libellé *</label><input type="text" name="label" required placeholder="Nom du produit"></div>
            <div class="field"><label>Prix HT (€)</label><input type="number" name="price" step="0.01" min="0" placeholder="0.00"></div>
            <div class="field"><label>Type</label>
              <select name="type">
                <option value="0">Produit</option>
                <option value="1" selected>Service</option>
              </select>
            </div>
            <div style="grid-column:span 4;">
              <button type="submit" class="btn btn-primary"><span class="material-icons-round" style="font-size:14px;">save</span>Créer</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Search bar -->
    <form method="GET" action="<?= APP_URL ?>/products" style="display:flex;gap:8px;margin-bottom:14px;">
      <input type="text" name="search" class="field" style="flex:1;padding:7px 11px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;" placeholder="Rechercher…" value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <button type="submit" class="btn btn-ghost">Filtrer</button>
      <?php if (!empty($search)): ?><a href="<?= APP_URL ?>/products" class="btn btn-ghost">Effacer</a><?php endif; ?>
    </form>

    <!-- Table -->
    <div class="panel" style="overflow:hidden;">
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead>
            <tr>
              <th>Référence</th>
              <th>Libellé</th>
              <th>Type</th>
              <th style="text-align:right;">Prix HT</th>
              <th style="text-align:center;">Abonnements actifs</th>
              <th style="text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($products)): ?>
            <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:32px;">Aucun produit. Créez-en un ci-dessus.</td></tr>
            <?php else: ?>
            <?php foreach ($products as $p): ?>
            <tr>
              <td style="font-family:monospace;font-size:12px;color:#64748b;"><?= htmlspecialchars($p['ref'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              <td style="font-weight:600;color:#0f172a;"><?= htmlspecialchars($p['label'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><span class="badge <?= (int)$p['type']===1 ? 'badge-blue' : 'badge-slate' ?>"><?= $typeLabel[(int)$p['type']] ?? 'Produit' ?></span></td>
              <td style="text-align:right;font-weight:700;"><?= $p['price'] > 0 ? number_format((float)$p['price'],2,',',' ').' €' : '–' ?></td>
              <td style="text-align:center;">
                <?php if ((int)$p['sub_count'] > 0): ?>
                <a href="<?= APP_URL ?>/subscriptions?search=<?= urlencode($p['label']) ?>" class="badge badge-green"><?= (int)$p['sub_count'] ?> actif<?= (int)$p['sub_count']>1?'s':'' ?></a>
                <?php else: ?>
                <span style="color:#94a3b8;font-size:12px;">–</span>
                <?php endif; ?>
              </td>
              <td style="text-align:right;">
                <a href="<?= APP_URL ?>/products?edit=<?= (int)$p['id'] ?>" class="btn btn-ghost" style="padding:4px 8px;font-size:11px;">
                  <span class="material-icons-round" style="font-size:13px;">edit</span>
                </a>
                <form method="POST" action="<?= APP_URL ?>/products/delete/<?= (int)$p['id'] ?>" style="display:inline;" onsubmit="return confirm('Supprimer ce produit ?')">
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

      <!-- Pagination -->
      <?php if ($pages > 1): ?>
      <div style="display:flex;align-items:center;justify-content:center;gap:4px;padding:12px;border-top:1px solid #f1f5f9;">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a href="?page=<?= $i ?><?= $search ? '&search='.urlencode($search) : '' ?>" class="pg-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
    </div>

  </main>
</div>

<script>
Vue.createApp({ data() { return { open: false } } }).mount('#add-app');
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
