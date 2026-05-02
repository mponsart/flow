<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php require_once __DIR__ . '/partials/sidebar.php'; ?>

<?php
// Labels lisibles
$recurrenceLabels = [
    'monthly'  => 'Mensuelle',
    'annual'   => 'Annuelle',
    'one_time' => 'Ponctuelle',
];
$recurrenceIcons = [
    'monthly'  => 'repeat',
    'annual'   => 'event_repeat',
    'one_time' => 'event',
];

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$editExpense = null;
if ($editId) {
    foreach ($expenses as $exp) {
        if ((int)$exp['id'] === $editId) { $editExpense = $exp; break; }
    }
}
?>

<div id="main">
  <header id="topbar">
    <h1><span class="material-icons" style="vertical-align:middle;margin-right:0.5rem;">account_balance_wallet</span>Dépenses & Rentabilité</h1>
    <div class="topbar-user">
      <?php if (!empty($user['avatar'])): ?>
        <img src="<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>" alt="Avatar">
      <?php endif; ?>
      <span><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </header>

  <div id="content">

    <?php if (!empty($_GET['message'])): ?>
    <div style="background:#e6f4ea;border:1px solid #a8d5b5;color:#1e8e3e;padding:0.75rem 1.25rem;border-radius:8px;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
      <span class="material-icons" style="font-size:1.1rem;">check_circle</span>
      <?= htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
    <div style="background:#fce8e6;border:1px solid #f5a9a3;color:#d93025;padding:0.75rem 1.25rem;border-radius:8px;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
      <span class="material-icons" style="font-size:1.1rem;">error</span>
      <?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>

    <!-- ────────────────────────────── Bloc rentabilité ────────────────────────── -->
    <div class="kpi-grid" style="margin-bottom:2rem;">

      <!-- Mensuel : revenus -->
      <div class="kpi-card">
        <div class="label">CA moyen / mois</div>
        <div class="value" style="color:var(--primary);"><?= number_format($revenueMonth, 0, ',', ' ') ?> €</div>
        <div class="sub">Factures payées – 12 derniers mois</div>
      </div>

      <!-- Mensuel : charges -->
      <div class="kpi-card">
        <div class="label">Charges / mois</div>
        <div class="value" style="color:var(--error);"><?= number_format($monthlyTotal, 0, ',', ' ') ?> €</div>
        <div class="sub">Équivalent mensuel de toutes les dépenses</div>
      </div>

      <!-- Mensuel : résultat -->
      <div class="kpi-card">
        <div class="label">Résultat mensuel</div>
        <?php
          $colorM = $profitMonth >= 0 ? 'var(--success)' : 'var(--error)';
          $signM  = $profitMonth >= 0 ? '+' : '';
        ?>
        <div class="value" style="color:<?= $colorM ?>;">
          <?= $signM . number_format($profitMonth, 0, ',', ' ') ?> €
        </div>
        <div class="sub">
          <?php if ($profitMonth >= 0): ?>
            <span style="color:var(--success)">✓ Rentable</span>
          <?php else: ?>
            <span style="color:var(--error)">✗ Déficitaire</span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Annuel : résultat -->
      <div class="kpi-card">
        <div class="label">Résultat annuel</div>
        <?php
          $colorY = $profitYear >= 0 ? 'var(--success)' : 'var(--error)';
          $signY  = $profitYear >= 0 ? '+' : '';
        ?>
        <div class="value" style="color:<?= $colorY ?>;">
          <?= $signY . number_format($profitYear, 0, ',', ' ') ?> €
        </div>
        <div class="sub">CA annuel – charges annualisées</div>
      </div>
    </div>

    <!-- ────────────────────────────── Répartition par catégorie ─────────────────── -->
    <?php if (!empty($byCategory)): ?>
    <div class="card" style="margin-bottom:1.5rem;">
      <p class="card-title">
        <span class="material-icons" style="vertical-align:middle;font-size:1rem;">donut_large</span>
        Charges mensuelles par catégorie
      </p>
      <div style="display:flex;flex-direction:column;gap:0.5rem;">
        <?php
          $maxCat = max($byCategory) ?: 1;
          foreach ($byCategory as $cat => $amt):
            $pct = round($amt / $monthlyTotal * 100);
        ?>
        <div style="display:flex;align-items:center;gap:1rem;">
          <div style="width:140px;font-size:0.875rem;flex-shrink:0;"><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></div>
          <div style="flex:1;background:#f1f3f4;border-radius:4px;overflow:hidden;height:18px;">
            <div style="width:<?= round($amt / $maxCat * 100) ?>%;background:var(--primary);height:100%;border-radius:4px;transition:width .3s;"></div>
          </div>
          <div style="width:100px;text-align:right;font-size:0.875rem;font-weight:500;"><?= number_format($amt, 0, ',', ' ') ?> €/mois</div>
          <div style="width:40px;text-align:right;font-size:0.8rem;color:#5f6368;"><?= $pct ?>%</div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;">

      <!-- ────────────── Formulaire ajout / édition ────────────── -->
      <div class="card">
        <p class="card-title">
          <span class="material-icons" style="vertical-align:middle;font-size:1rem;"><?= $editExpense ? 'edit' : 'add_circle' ?></span>
          <?= $editExpense ? 'Modifier la dépense' : 'Ajouter une dépense' ?>
        </p>

        <form method="POST" action="<?= APP_URL ?>/expenses/<?= $editExpense ? 'update/' . $editExpense['id'] : 'store' ?>" style="display:flex;flex-direction:column;gap:0.875rem;">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

          <div>
            <label style="font-size:0.8125rem;color:#5f6368;">Libellé *</label>
            <input type="text" name="label" required
                   value="<?= htmlspecialchars($editExpense['label'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   placeholder="ex. Loyer bureau"
                   style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--outline);border-radius:6px;font-size:0.9rem;margin-top:4px;">
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
            <div>
              <label style="font-size:0.8125rem;color:#5f6368;">Montant (€) *</label>
              <input type="number" name="amount" required min="0.01" step="0.01"
                     value="<?= $editExpense ? number_format((float)$editExpense['amount'], 2, '.', '') : '' ?>"
                     placeholder="0.00"
                     style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--outline);border-radius:6px;font-size:0.9rem;margin-top:4px;">
            </div>
            <div>
              <label style="font-size:0.8125rem;color:#5f6368;">Récurrence *</label>
              <select name="recurrence" style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--outline);border-radius:6px;font-size:0.9rem;margin-top:4px;background:white;">
                <?php foreach ($recurrenceLabels as $val => $lbl): ?>
                  <option value="<?= $val ?>" <?= ($editExpense['recurrence'] ?? 'monthly') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
            <div>
              <label style="font-size:0.8125rem;color:#5f6368;">Catégorie</label>
              <input type="text" name="category" list="cat-list"
                     value="<?= htmlspecialchars($editExpense['category'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                     placeholder="Loyer, Salaire, Logiciel…"
                     style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--outline);border-radius:6px;font-size:0.9rem;margin-top:4px;">
              <datalist id="cat-list">
                <?php
                  $cats = ['Loyer', 'Salaire', 'Charges sociales', 'Assurance', 'Logiciel', 'Matériel', 'Fournitures', 'Marketing', 'Transport', 'Autre'];
                  foreach ($cats as $c): ?>
                  <option value="<?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>">
                <?php endforeach; ?>
              </datalist>
            </div>
            <div>
              <label style="font-size:0.8125rem;color:#5f6368;">Date (ponctuelle)</label>
              <input type="date" name="expense_date"
                     value="<?= htmlspecialchars($editExpense['expense_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                     style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--outline);border-radius:6px;font-size:0.9rem;margin-top:4px;">
            </div>
          </div>

          <div>
            <label style="font-size:0.8125rem;color:#5f6368;">Note (optionnel)</label>
            <textarea name="note" rows="2" placeholder="Commentaire libre…"
                      style="width:100%;padding:0.5rem 0.75rem;border:1px solid var(--outline);border-radius:6px;font-size:0.9rem;margin-top:4px;resize:vertical;"><?= htmlspecialchars($editExpense['note'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>

          <div style="display:flex;gap:0.75rem;margin-top:0.25rem;">
            <button type="submit" class="btn btn-primary" style="flex:1;">
              <span class="material-icons" style="font-size:1rem;"><?= $editExpense ? 'save' : 'add' ?></span>
              <?= $editExpense ? 'Enregistrer' : 'Ajouter' ?>
            </button>
            <?php if ($editExpense): ?>
            <a href="<?= APP_URL ?>/expenses" class="btn btn-outline" style="flex:1;text-align:center;text-decoration:none;">Annuler</a>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <!-- ────────────── Liste des dépenses ────────────── -->
      <div class="card">
        <p class="card-title">
          <span class="material-icons" style="vertical-align:middle;font-size:1rem;">list</span>
          Toutes les dépenses (<?= count($expenses) ?>)
        </p>

        <?php if (empty($expenses)): ?>
          <p style="color:#5f6368;font-size:0.9rem;text-align:center;padding:2rem 0;">Aucune dépense enregistrée.</p>
        <?php else: ?>
          <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
              <thead>
                <tr style="border-bottom:2px solid var(--outline);">
                  <th style="text-align:left;padding:0.5rem 0.75rem;color:#5f6368;font-weight:500;">Libellé</th>
                  <th style="text-align:left;padding:0.5rem 0.75rem;color:#5f6368;font-weight:500;">Catégorie</th>
                  <th style="text-align:right;padding:0.5rem 0.75rem;color:#5f6368;font-weight:500;">Montant</th>
                  <th style="text-align:center;padding:0.5rem 0.75rem;color:#5f6368;font-weight:500;">Récurrence</th>
                  <th style="text-align:center;padding:0.5rem 0.75rem;color:#5f6368;font-weight:500;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($expenses as $exp):
                  $isEditing = $editId === (int)$exp['id'];
                ?>
                <tr style="border-bottom:1px solid var(--outline);background:<?= $isEditing ? '#e8f0fe' : 'transparent' ?>;">
                  <td style="padding:0.5rem 0.75rem;">
                    <?= htmlspecialchars($exp['label'], ENT_QUOTES, 'UTF-8') ?>
                    <?php if ($exp['note']): ?>
                      <span class="material-icons" style="font-size:0.9rem;color:#5f6368;vertical-align:middle;" title="<?= htmlspecialchars($exp['note'], ENT_QUOTES, 'UTF-8') ?>">info</span>
                    <?php endif; ?>
                  </td>
                  <td style="padding:0.5rem 0.75rem;color:#5f6368;"><?= htmlspecialchars($exp['category'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td style="padding:0.5rem 0.75rem;text-align:right;font-weight:500;"><?= number_format((float)$exp['amount'], 2, ',', ' ') ?> €</td>
                  <td style="padding:0.5rem 0.75rem;text-align:center;">
                    <span style="display:inline-flex;align-items:center;gap:0.25rem;background:#f1f3f4;border-radius:100px;padding:0.2rem 0.6rem;font-size:0.8rem;">
                      <span class="material-icons" style="font-size:0.85rem;"><?= $recurrenceIcons[$exp['recurrence']] ?? 'event' ?></span>
                      <?= $recurrenceLabels[$exp['recurrence']] ?? $exp['recurrence'] ?>
                    </span>
                  </td>
                  <td style="padding:0.5rem 0.75rem;text-align:center;white-space:nowrap;">
                    <a href="<?= APP_URL ?>/expenses?edit=<?= (int)$exp['id'] ?>"
                       title="Modifier"
                       style="color:var(--primary);text-decoration:none;margin-right:0.5rem;">
                      <span class="material-icons" style="font-size:1.1rem;vertical-align:middle;">edit</span>
                    </a>
                    <form method="POST" action="<?= APP_URL ?>/expenses/delete/<?= (int)$exp['id'] ?>"
                          style="display:inline;"
                          onsubmit="return confirm('Supprimer cette dépense ?');">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                      <button type="submit" title="Supprimer"
                              style="background:none;border:none;cursor:pointer;color:var(--error);padding:0;vertical-align:middle;">
                        <span class="material-icons" style="font-size:1.1rem;">delete</span>
                      </button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr style="border-top:2px solid var(--outline);background:#f8f9fa;">
                  <td colspan="2" style="padding:0.5rem 0.75rem;font-weight:500;">Total mensuel équivalent</td>
                  <td style="padding:0.5rem 0.75rem;text-align:right;font-weight:700;color:var(--error);"><?= number_format($monthlyTotal, 0, ',', ' ') ?> €/mois</td>
                  <td colspan="2" style="padding:0.5rem 0.75rem;text-align:center;color:#5f6368;font-size:0.8rem;">soit <?= number_format($annualTotal, 0, ',', ' ') ?> €/an</td>
                </tr>
              </tfoot>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div><!-- /grid -->

  </div><!-- /content -->
</div><!-- /main -->

<?php require_once __DIR__ . '/partials/footer.php'; ?>
