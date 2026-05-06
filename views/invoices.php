<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<?php
$statusLabel = [0=>'Brouillon',1=>'Validée',2=>'Payée',3=>'Abandonnée'];
$statusCss   = [0=>'badge-slate',1=>'badge-amber',2=>'badge-green',3=>'badge-red'];
$csrf = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<div id="main-wrap" class="flex-1 flex flex-col overflow-hidden">

  <div class="topbar flex items-center justify-between px-6 h-14 flex-shrink-0 sticky top-0 z-20">
    <div style="display:flex;align-items:center;gap:10px;">
      <button id="menu-toggle" class="lg:hidden" style="background:none;border:none;cursor:pointer;padding:4px;">
        <span class="material-icons-round" style="color:#64748b;font-size:20px;">menu</span>
      </button>
      <span style="font-size:15px;font-weight:700;color:#0f172a;">Factures</span>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
      <?php if (!empty($user['avatar'])): ?>
      <img src="<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>" style="width:28px;height:28px;border-radius:50%;" alt="">
      <?php endif; ?>
      <span style="font-size:13px;font-weight:500;color:#475569;"><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </div>

  <main class="flex-1 overflow-y-auto" style="padding:20px 24px;" id="invoices-page" v-cloak>

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

    <!-- Toolbar -->
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
      <form method="GET" action="<?= APP_URL ?>/invoices" style="display:flex;align-items:center;gap:6px;flex:1;min-width:0;">
        <div style="position:relative;flex:1;max-width:300px;">
          <span class="material-icons-round" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:16px;pointer-events:none;">search</span>
          <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Référence ou client…" style="width:100%;padding:7px 10px 7px 34px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;background:#fff;font-family:inherit;outline:none;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
        </div>
        <button type="submit" class="btn btn-ghost">Filtrer</button>
      </form>
      <button @click="showAdd=!showAdd" class="btn btn-primary">
        <span class="material-icons-round" style="font-size:16px;">add</span> Nouvelle facture
      </button>
    </div>

    <!-- Add form -->
    <div v-show="showAdd" class="panel" style="margin-bottom:16px;padding:20px;">
      <div style="font-size:13px;font-weight:600;color:#0f172a;margin-bottom:16px;display:flex;align-items:center;gap:6px;">
        <span class="material-icons-round" style="font-size:16px;color:#2563eb;">add_circle</span> Nouvelle facture
      </div>
      <form method="POST" action="<?= APP_URL ?>/invoices/store" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="field">
          <label>Client</label>
          <select name="tiers_id">
            <option value="">— Sans client —</option>
            <?php foreach ($tiersAll as $t): ?>
            <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label>Montant HT (€) *</label><input type="number" name="total_ht" step="0.01" min="0.01" placeholder="0.00" required></div>
        <div class="field"><label>Date *</label><input type="date" name="date_invoice" value="<?= date('Y-m-d') ?>" required></div>
        <div class="field">
          <label>Statut</label>
          <select name="status">
            <option value="2" selected>Payée</option>
            <option value="1">Validée</option>
            <option value="0">Brouillon</option>
          </select>
        </div>
        <div style="grid-column:1/-1;display:flex;gap:8px;padding-top:4px;">
          <button type="submit" class="btn btn-primary"><span class="material-icons-round" style="font-size:15px;">save</span> Créer</button>
          <button type="button" @click="showAdd=false" class="btn btn-ghost">Annuler</button>
        </div>
      </form>
    </div>

    <!-- Table -->
    <div class="panel" style="overflow:hidden;">
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead><tr>
            <th>Référence</th>
            <th>Client</th>
            <th>Date</th>
            <th>Échéance</th>
            <th style="text-align:right;">Total HT</th>
            <th>Statut</th>
            <th style="text-align:right;">Actions</th>
          </tr></thead>
          <tbody>
            <?php if (empty($invoices)): ?>
            <tr><td colspan="7" style="text-align:center;padding:32px;color:#94a3b8;">Aucune facture. Créez-en une avec le bouton ci-dessus.</td></tr>
            <?php else: ?>
            <?php foreach ($invoices as $inv): ?>
            <tr>
              <td style="font-weight:700;white-space:nowrap;"><?= htmlspecialchars($inv['ref'], ENT_QUOTES, 'UTF-8') ?></td>
              <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($inv['tiers_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
              <td style="white-space:nowrap;color:#64748b;font-size:12px;"><?= $inv['date_invoice'] ? date('d/m/Y', strtotime($inv['date_invoice'])) : '–' ?></td>
              <td style="white-space:nowrap;font-size:12px;<?= $inv['is_overdue'] ? 'color:#dc2626;font-weight:600;' : 'color:#64748b;' ?>">
                <?= $inv['date_due'] ? date('d/m/Y', strtotime($inv['date_due'])) : '–' ?>
                <?php if ($inv['is_overdue']): ?><span class="badge badge-red" style="margin-left:4px;">Retard</span><?php endif; ?>
              </td>
              <td style="text-align:right;font-weight:700;white-space:nowrap;"><?= number_format((float)$inv['total_ht'], 2, ',', ' ') ?> €</td>
              <td><span class="badge <?= $statusCss[$inv['status']] ?? 'badge-slate' ?>"><?= $statusLabel[$inv['status']] ?? '–' ?></span></td>
              <td style="text-align:right;">
                <div style="display:flex;align-items:center;justify-content:flex-end;gap:4px;">
                  <?php if ((int)$inv['status'] !== 2): ?>
                  <form method="POST" action="<?= APP_URL ?>/invoices/pay/<?= (int)$inv['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <button type="submit" class="btn btn-ghost" style="padding:5px 10px;font-size:12px;color:#16a34a;border-color:#bbf7d0;" title="Marquer payée">
                      <span class="material-icons-round" style="font-size:13px;">check_circle</span>
                    </button>
                  </form>
                  <?php endif; ?>
                  <button @click="confirmDelete={id:<?= (int)$inv['id'] ?>,ref:'<?= htmlspecialchars(addslashes($inv['ref']), ENT_QUOTES, 'UTF-8') ?>'}" class="btn btn-danger" style="padding:5px 10px;font-size:12px;">
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

      <?php if ($pages > 1): ?>
      <div style="padding:10px 16px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:12px;color:#94a3b8;"><?= $total ?> factures · page <?= $page ?>/<?= $pages ?></span>
        <div style="display:flex;gap:4px;">
          <?php if ($page > 1): ?>
          <a href="?page=<?= $page-1 ?>&search=<?= urlencode($_GET['search']??'') ?>" class="pg-btn">‹</a>
          <?php endif; ?>
          <?php for ($p = max(1,$page-2); $p <= min($pages,$page+2); $p++): ?>
          <a href="?page=<?= $p ?>&search=<?= urlencode($_GET['search']??'') ?>" class="pg-btn <?= $p===$page?'active':'' ?>"><?= $p ?></a>
          <?php endfor; ?>
          <?php if ($page < $pages): ?>
          <a href="?page=<?= $page+1 ?>&search=<?= urlencode($_GET['search']??'') ?>" class="pg-btn">›</a>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Delete modal -->
    <div v-if="confirmDelete" style="position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:flex;align-items:center;justify-content:center;padding:16px;" @click.self="confirmDelete=null">
      <div style="background:#fff;border-radius:12px;box-shadow:0 25px 50px rgba(0,0,0,.2);width:100%;max-width:380px;padding:24px;" @click.stop>
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
          <div style="width:40px;height:40px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <span class="material-icons-round" style="color:#dc2626;font-size:20px;">delete_forever</span>
          </div>
          <div>
            <div style="font-size:15px;font-weight:700;color:#0f172a;">Supprimer la facture ?</div>
            <div style="font-size:13px;color:#64748b;margin-top:2px;">{{ confirmDelete.ref }}</div>
          </div>
        </div>
        <p style="font-size:13px;color:#64748b;margin-bottom:20px;">Les paiements associés seront également supprimés.</p>
        <div style="display:flex;gap:8px;">
          <button @click="confirmDelete=null" class="btn btn-ghost" style="flex:1;justify-content:center;">Annuler</button>
          <form :action="'<?= APP_URL ?>/invoices/delete/' + confirmDelete.id" method="POST" style="flex:1;">
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
}).mount('#invoices-page');
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
