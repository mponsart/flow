<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$navItems = [
  ['path'=>'/',         'icon'=>'dashboard',             'label'=>'Tableau de bord'],
  ['path'=>'/tiers',    'icon'=>'groups',                'label'=>'Tiers'],
  ['path'=>'/invoices', 'icon'=>'receipt_long',          'label'=>'Factures'],
  ['path'=>'/payments', 'icon'=>'payments',              'label'=>'Paiements'],
  ['path'=>'/forecast', 'icon'=>'trending_up',           'label'=>'Prévisions'],
  ['path'=>'/expenses', 'icon'=>'account_balance_wallet','label'=>'Dépenses'],
];
?>
<aside id="sidebar" class="w-64 flex-shrink-0 bg-slate-900 flex flex-col z-30 h-screen shadow-2xl">

  <!-- Brand -->
  <div class="flex items-center justify-between px-5 py-4 border-b border-slate-700/70">
    <div class="flex items-center gap-2.5">
      <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-900/40">
        <span class="material-icons-round text-white" style="font-size:1.125rem;">water_drop</span>
      </div>
      <div>
        <p class="text-white font-bold text-lg leading-none">Flow</p>
        <p class="text-slate-300/80 text-xs leading-tight mt-0.5">Groupe Speed Cloud</p>
      </div>
    </div>
    <button id="sidebar-close" class="lg:hidden p-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-white/10 transition-colors">
      <span class="material-icons-round text-xl">close</span>
    </button>
  </div>

  <!-- Nav -->
  <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-0.5">
    <p class="px-3 pb-2 text-xs font-semibold text-slate-300/70 uppercase tracking-widest">Navigation</p>
    <?php foreach ($navItems as $item):
      $active = ($currentPath === $item['path'])
             || ($item['path'] !== '/' && str_starts_with($currentPath, $item['path']));
    ?>
    <a href="<?= APP_URL . $item['path'] ?>"
      class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all
        <?= $active ? 'bg-gradient-to-r from-blue-600 to-sky-500 text-white shadow-lg shadow-blue-950/40' : 'text-slate-300/85 hover:text-white hover:bg-white/10' ?>">
      <span class="material-icons-round text-xl leading-none"><?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?></span>
      <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
    </a>
    <?php endforeach; ?>

    <p class="px-3 pt-5 pb-2 text-xs font-semibold text-slate-300/70 uppercase tracking-widest">Exports</p>
    <?php foreach ([
      ['/export/csv?type=invoices','download','Factures CSV'],
      ['/export/csv?type=payments','download','Paiements CSV'],
      ['/export/csv?type=tiers',  'download','Tiers CSV'],
      ['/export/pdf',             'picture_as_pdf','Rapport PDF'],
    ] as [$href, $icon, $label]): ?>
    <a href="<?= APP_URL . $href ?>" <?= str_contains($href,'pdf') ? 'target="_blank"' : '' ?>
      class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-slate-300/85 hover:text-white hover:bg-white/10 transition-colors">
      <span class="material-icons-round text-base leading-none"><?= $icon ?></span>
      <?= $label ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <!-- User + logout -->
  <div class="px-4 py-4 border-t border-slate-700/70 bg-black/15">
    <?php if (!empty($_SESSION['user'])): $u = $_SESSION['user']; ?>
    <div class="flex items-center gap-3 mb-3">
      <?php if (!empty($u['avatar'])): ?>
      <img src="<?= htmlspecialchars($u['avatar'], ENT_QUOTES, 'UTF-8') ?>"
           alt="Avatar" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
      <?php else: ?>
      <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
        <?= strtoupper(substr($u['name'] ?? 'U', 0, 1)) ?>
      </div>
      <?php endif; ?>
      <div class="min-w-0">
        <p class="text-sm font-medium text-white truncate"><?= htmlspecialchars($u['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
        <p class="text-xs text-slate-300/80 truncate"><?= htmlspecialchars($u['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
      </div>
    </div>
    <form method="POST" action="<?= APP_URL ?>/logout">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-slate-200 hover:text-white hover:bg-white/10 rounded-xl transition-colors">
        <span class="material-icons-round text-base">logout</span> Se déconnecter
      </button>
    </form>
    <?php endif; ?>
  </div>
</aside>

<div id="sidebar-overlay"></div>
