<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<?php
$editTiersId = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$editTiers   = null;
if ($editTiersId) {
  foreach ($tiers as $t) {
    if ((int)$t['id'] === $editTiersId) { $editTiers = $t; break; }
  }
}
$riskCss = ['low'=>'badge-green','medium'=>'badge-amber','high'=>'badge-red'];
$riskLabel = ['low'=>'Faible','medium'=>'Modéré','high'=>'Élevé'];
$csrf = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
$highCount = count(array_filter($tiers, fn($t) => ($t['risk_level']??'') === 'high'));
$medCount  = count(array_filter($tiers, fn($t) => ($t['risk_level']??'') === 'medium'));
?>

<div id="main-wrap" class="flex-1 flex flex-col overflow-hidden">

  <div class="topbar flex items-center justify-between px-6 h-14 flex-shrink-0 sticky top-0 z-20">
    <div style="display:flex;align-items:center;gap:10px;">
      <button id="menu-toggle" class="lg:hidden" style="background:none;border:none;cursor:pointer;padding:4px;">
        <span class="material-icons-round" style="color:#64748b;font-size:20px;">menu</span>
      </button>
      <span style="font-size:15px;font-weight:700;color:#0f172a;">Tiers</span>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
      <?php if (!empty($user['avatar'])): ?>
      <img src="<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>" style="width:28px;height:28px;border-radius:50%;" alt="">
      <?php endif; ?>
      <span style="font-size:13px;font-weight:500;color:#475569;"><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </div>

  <main class="flex-1 overflow-y-auto" style="padding:20px 24px;" id="tiers-page" v-cloak>

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
      <form method="GET" action="<?= APP_URL ?>/tiers" style="display:flex;align-items:center;gap:6px;flex:1;min-width:0;">
        <div style="position:relative;flex:1;max-width:280px;">
          <span class="material-icons-round" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:16px;pointer-events:none;">search</span>
          <input type="text" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Rechercher…" style="width:100%;padding:7px 10px 7px 34px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;background:#fff;font-family:inherit;outline:none;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
        </div>
        <select name="level" style="padding:7px 10px;border:1px solid #e2e8f0;border-radius:6px;font-size:13px;background:#fff;font-family:inherit;color:#334155;outline:none;">
          <option value="">Tous les risques</option>
          <option value="low" <?= $level==='low'?'selected':'' ?>>Faible</option>
          <option value="medium" <?= $level==='medium'?'selected':'' ?>>Modéré</option>
          <option value="high" <?= $level==='high'?'selected':'' ?>>Élevé</option>
        </select>
        <button type="submit" class="btn btn-ghost">Filtrer</button>
      </form>
      <button @click="showAdd=!showAdd" class="btn btn-primary">
        <span class="material-icons-round" style="font-size:16px;">person_add</span> Nouveau
      </button>
      <a href="<?= APP_URL ?>/export/csv?type=tiers" class="btn btn-ghost">
        <span class="material-icons-round" style="font-size:16px;">download</span> CSV
      </a>
    </div>

    <!-- Add form -->
    <div v-show="showAdd" class="panel" style="margin-bottom:16px;padding:20px;">
      <div style="font-size:13px;font-weight:600;color:#0f172a;margin-bottom:16px;display:flex;align-items:center;gap:6px;">
        <span class="material-icons-round" style="font-size:16px;color:#2563eb;">person_add</span> Nouveau tiers
      </div>
      <form method="POST" action="<?= APP_URL ?>/tiers/store" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="field"><label>Nom *</label><input type="text" name="name" required placeholder="Nom du tiers"></div>
        <div class="field"><label>Email</label><input type="email" name="email" placeholder="contact@example.com"></div>
        <div style="grid-column:1/-1;display:flex;gap:8px;padding-top:4px;">
          <button type="submit" class="btn btn-primary"><span class="material-icons-round" style="font-size:15px;">save</span> Créer</button>
          <button type="button" @click="showAdd=false" class="btn btn-ghost">Annuler</button>
        </div>
      </form>
    </div>

    <!-- Edit form -->
    <?php if ($editTiers): ?>
    <div class="panel" style="margin-bottom:16px;padding:20px;border-color:#3b82f6;border-width:2px;">
      <div style="font-size:13px;font-weight:600;color:#0f172a;margin-bottom:16px;display:flex;align-items:center;gap:6px;">
        <span class="material-icons-round" style="font-size:16px;color:#2563eb;">edit</span>
        Modifier : <?= htmlspecialchars($editTiers['name'], ENT_QUOTES, 'UTF-8') ?>
      </div>
      <form method="POST" action="<?= APP_URL ?>/tiers/update/<?= (int)$editTiers['id'] ?>" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="field"><label>Nom *</label><input type="text" name="name" required value="<?= htmlspecialchars($editTiers['name'], ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="field"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($editTiers['email']??'', ENT_QUOTES, 'UTF-8') ?>"></div>
        <div style="grid-column:1/-1;display:flex;gap:8px;padding-top:4px;">
          <button type="submit" class="btn btn-primary"><span class="material-icons-round" style="font-size:15px;">save</span> Enregistrer</button>
          <a href="<?= APP_URL ?>/tiers?search=<?= urlencode($search) ?>&level=<?= urlencode($level) ?>" class="btn btn-ghost">Annuler</a>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px;">
      <div class="stat-card accent-blue">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:8px;">Total</div>
        <div style="font-size:28px;font-weight:800;color:#0f172a;line-height:1;"><?= $total ?></div>
        <div style="font-size:12px;color:#64748b;margin-top:6px;">tiers enregistrés</div>
      </div>
      <div class="stat-card accent-amber">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:8px;">Risque modéré</div>
        <div style="font-size:28px;font-weight:800;color:#d97706;line-height:1;"><?= $medCount ?></div>
        <div style="font-size:12px;color:#64748b;margin-top:6px;">sur cette page</div>
      </div>
      <div class="stat-card accent-red">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:8px;">Risque élevé</div>
        <div style="font-size:28px;font-weight:800;color:#dc2626;line-height:1;"><?= $highCount ?></div>
        <div style="font-size:12px;color:#64748b;margin-top:6px;">sur cette page</div>
      </div>
    </div>

    <!-- Table -->
    <div class="panel" style="overflow:hidden;">
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead><tr>
            <th>Tiers</th>
            <th style="text-align:right;">CA</th>
            <th style="text-align:center;">Fct.</th>
            <th style="text-align:center;">Retards</th>
            <th>Dernière fct.</th>
            <th>Risque</th>
            <th style="text-align:right;">Actions</th>
          </tr></thead>
          <tbody>
            <?php if (empty($tiers)): ?>
            <tr><td colspan="7" style="text-align:center;padding:32px;color:#94a3b8;">Aucun tiers trouvé.</td></tr>
            <?php else: ?>
            <?php foreach ($tiers as $t):
              $rl = $t['risk_level'] ?? 'low';
              $score = (int)($t['risk_score'] ?? 0);
            ?>
            <tr>
              <td>
                <div style="font-weight:600;color:#0f172a;"><?= htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php if (!empty($t['email'])): ?>
                <div style="font-size:11px;color:#94a3b8;margin-top:2px;"><?= htmlspecialchars($t['email'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
              </td>
              <td style="text-align:right;font-weight:700;white-space:nowrap;"><?= number_format((float)$t['revenue'], 0, ',', ' ') ?> €</td>
              <td style="text-align:center;"><?= (int)$t['invoice_count'] ?></td>
              <td style="text-align:center;">
                <?php if ((int)$t['overdue_count'] > 0): ?>
                <span class="badge badge-red"><?= (int)$t['overdue_count'] ?> en retard</span>
                <?php else: ?>
                <span class="badge badge-green">OK</span>
                <?php endif; ?>
              </td>
              <td style="white-space:nowrap;color:#64748b;font-size:12px;">
                <?= $t['last_invoice_date'] ? date('d/m/Y', strtotime($t['last_invoice_date'])) : '–' ?>
              </td>
              <td>
                <span class="badge <?= $riskCss[$rl] ?? 'badge-slate' ?>"><?= $riskLabel[$rl] ?? $rl ?> (<?= $score ?>)</span>
              </td>
              <td style="text-align:right;">
                <div style="display:flex;align-items:center;justify-content:flex-end;gap:4px;">
                  <a href="<?= APP_URL ?>/tiers/<?= (int)$t['id'] ?>" class="btn btn-ghost" style="padding:5px 10px;font-size:12px;">
                    <span class="material-icons-round" style="font-size:13px;">visibility</span>
                  </a>
                  <a href="<?= APP_URL ?>/tiers?edit=<?= (int)$t['id'] ?>&search=<?= urlencode($search) ?>&level=<?= urlencode($level) ?>" class="btn btn-ghost" style="padding:5px 10px;font-size:12px;">
                    <span class="material-icons-round" style="font-size:13px;">edit</span>
                  </a>
                  <button @click="confirmDelete={id:<?= (int)$t['id'] ?>,name:'<?= htmlspecialchars(addslashes($t['name']), ENT_QUOTES, 'UTF-8') ?>'}" class="btn btn-danger" style="padding:5px 10px;font-size:12px;">
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
        <span style="font-size:12px;color:#94a3b8;">Page <?= $page ?> / <?= $pages ?></span>
        <div style="display:flex;gap:4px;">
          <?php if ($page > 1): ?>
          <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&level=<?= urlencode($level) ?>" class="pg-btn">‹</a>
          <?php endif; ?>
          <?php for ($p = max(1,$page-2); $p <= min($pages,$page+2); $p++): ?>
          <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&level=<?= urlencode($level) ?>" class="pg-btn <?= $p===$page?'active':'' ?>"><?= $p ?></a>
          <?php endfor; ?>
          <?php if ($page < $pages): ?>
          <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&level=<?= urlencode($level) ?>" class="pg-btn">›</a>
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
            <div style="font-size:15px;font-weight:700;color:#0f172a;">Supprimer ce tiers ?</div>
            <div style="font-size:13px;color:#64748b;margin-top:2px;">{{ confirmDelete.name }}</div>
          </div>
        </div>
        <p style="font-size:13px;color:#64748b;margin-bottom:20px;">Les factures et paiements associés seront désassociés mais conservés.</p>
        <div style="display:flex;gap:8px;">
          <button @click="confirmDelete=null" class="btn btn-ghost" style="flex:1;justify-content:center;">Annuler</button>
          <form :action="'<?= APP_URL ?>/tiers/delete/' + confirmDelete.id" method="POST" style="flex:1;">
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
}).mount('#tiers-page');
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
