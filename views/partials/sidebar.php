<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$navItems = [
    ['path' => '/',          'icon' => 'dashboard',            'label' => 'Tableau de bord'],
    ['path' => '/tiers',     'icon' => 'groups',               'label' => 'Tiers'],
    ['path' => '/payments',  'icon' => 'payments',             'label' => 'Paiements'],
    ['path' => '/forecast',  'icon' => 'trending_up',          'label' => 'Prévisions'],
    ['path' => '/expenses',  'icon' => 'account_balance_wallet','label' => 'Dépenses'],
    ['path' => '/sync',      'icon' => 'sync',                 'label' => 'Synchronisation'],
];
?>
<nav id="sidebar">
  <!-- Logo -->
  <div style="padding: 1.5rem 1.5rem 1rem; border-bottom: 1px solid var(--outline);">
    <div style="display:flex;align-items:center;gap:0.75rem;">
      <span class="material-icons" style="color:var(--primary);font-size:2rem;">water_drop</span>
      <div>
        <div style="font-size:1.25rem;font-weight:700;color:var(--primary);">Flow</div>
        <div style="font-size:0.7rem;color:#5f6368;text-transform:uppercase;letter-spacing:1px;">Groupe Speed</div>
      </div>
    </div>
  </div>

  <!-- Navigation -->
  <ul style="list-style:none;margin:1rem 0;padding:0;">
    <?php foreach ($navItems as $item):
      $isActive = ($currentPath === $item['path'])
               || ($item['path'] !== '/' && str_starts_with($currentPath, $item['path']));
    ?>
    <li>
      <a href="<?= APP_URL . $item['path'] ?>"
         style="display:flex;align-items:center;gap:0.875rem;padding:0.75rem 1.25rem;text-decoration:none;
                color:<?= $isActive ? 'var(--primary)' : '#444' ?>;
                background:<?= $isActive ? '#e8f0fe' : 'transparent' ?>;
                border-radius:0 100px 100px 0;margin-right:0.75rem;font-weight:<?= $isActive ? 500 : 400 ?>;
                transition:background 0.15s;font-size:0.9rem;">
        <span class="material-icons" style="font-size:1.25rem;"><?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?></span>
        <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>

  <!-- Separator -->
  <div style="border-top:1px solid var(--outline);margin:0.5rem 1.25rem;"></div>

  <!-- Export links -->
  <div style="padding:0.5rem 1.25rem;">
    <p style="font-size:0.75rem;color:#5f6368;text-transform:uppercase;letter-spacing:0.5px;margin:0.5rem 0;">Exports</p>
    <a href="<?= APP_URL ?>/export/csv?type=invoices" style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 0;color:#444;text-decoration:none;font-size:0.875rem;">
      <span class="material-icons" style="font-size:1rem;">download</span> Factures CSV
    </a>
    <a href="<?= APP_URL ?>/export/csv?type=tiers" style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 0;color:#444;text-decoration:none;font-size:0.875rem;">
      <span class="material-icons" style="font-size:1rem;">download</span> Tiers CSV
    </a>
    <a href="<?= APP_URL ?>/export/csv?type=payments" style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 0;color:#444;text-decoration:none;font-size:0.875rem;">
      <span class="material-icons" style="font-size:1rem;">download</span> Paiements CSV
    </a>
    <a href="<?= APP_URL ?>/export/pdf" target="_blank" style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 0;color:#444;text-decoration:none;font-size:0.875rem;">
      <span class="material-icons" style="font-size:1rem;">picture_as_pdf</span> Rapport PDF
    </a>
  </div>

  <!-- Spacer -->
  <div style="flex:1;"></div>

  <!-- Logout -->
  <div style="padding:1rem 1.25rem;border-top:1px solid var(--outline);margin-top:auto;">
    <?php if (!empty($_SESSION['user'])): ?>
    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;">
      <?php if (!empty($_SESSION['user']['avatar'])): ?>
      <img src="<?= htmlspecialchars($_SESSION['user']['avatar'], ENT_QUOTES, 'UTF-8') ?>"
           alt="Avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
      <?php endif; ?>
      <div>
        <div style="font-size:0.8125rem;font-weight:500;"><?= htmlspecialchars($_SESSION['user']['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
        <div style="font-size:0.75rem;color:#5f6368;"><?= htmlspecialchars($_SESSION['user']['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
      </div>
    </div>
    <form method="POST" action="<?= APP_URL ?>/logout" class="logout-form">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <button type="submit" class="btn btn-outline" style="width:100%;justify-content:center;">
        <span class="material-icons" style="font-size:1rem;">logout</span> Déconnexion
      </button>
    </form>
    <?php endif; ?>
  </div>
</nav>
