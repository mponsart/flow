<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Flow – Pilotage financier</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans:    ['Inter', 'system-ui', 'sans-serif'],
            display: ['Inter', 'system-ui', 'sans-serif']
          }
        }
      }
    }
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons+Round">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>

  <style>
    * { box-sizing: border-box; }
    body {
      font-family: 'Inter', system-ui, sans-serif;
      background: #f1f5f9;
      color: #0f172a;
      margin: 0;
    }
    [v-cloak] { display: none; }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 4px; height: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

    /* Sidebar transitions */
    #sidebar { transition: transform .22s ease; }
    #sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:25; }
    #sidebar-overlay.active { display:block; }
    @media (max-width: 1024px) {
      #sidebar { transform: translateX(-100%); position: fixed; top:0; bottom:0; left:0; }
      #sidebar.open { transform: translateX(0); }
      #main-wrap { margin-left: 0 !important; }
    }

    /* Topbar glass */
    .topbar {
      background: rgba(255,255,255,.95);
      backdrop-filter: blur(8px);
      border-bottom: 1px solid #e2e8f0;
    }

    /* KPI stat card */
    .stat-card {
      background: #fff;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 16px 20px;
    }
    .stat-card.accent-blue  { border-left: 3px solid #3b82f6; }
    .stat-card.accent-sky   { border-left: 3px solid #0ea5e9; }
    .stat-card.accent-green { border-left: 3px solid #10b981; }
    .stat-card.accent-amber { border-left: 3px solid #f59e0b; }
    .stat-card.accent-red   { border-left: 3px solid #ef4444; }
    .stat-card.accent-violet{ border-left: 3px solid #8b5cf6; }

    /* Panel card */
    .panel {
      background: #fff;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
    }
    .panel-head {
      padding: 12px 16px;
      border-bottom: 1px solid #f1f5f9;
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      font-weight: 600;
      color: #0f172a;
    }

    /* Form inputs */
    .field input, .field select, .field textarea {
      width: 100%;
      padding: 7px 11px;
      font-size: 13px;
      font-family: inherit;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      background: #fff;
      color: #0f172a;
      outline: none;
      transition: border-color .15s;
    }
    .field input:focus, .field select:focus, .field textarea:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59,130,246,.1);
    }
    .field label {
      display: block;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: #64748b;
      margin-bottom: 4px;
    }

    /* Buttons */
    .btn { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; font-size:13px; font-weight:600; border-radius:6px; cursor:pointer; transition:background .15s, border-color .15s; border:1px solid transparent; font-family:inherit; }
    .btn-primary { background:#2563eb; color:#fff; }
    .btn-primary:hover { background:#1d4ed8; }
    .btn-ghost { background:#fff; color:#334155; border-color:#e2e8f0; }
    .btn-ghost:hover { background:#f8fafc; }
    .btn-danger { background:#fff; color:#dc2626; border-color:#fecaca; }
    .btn-danger:hover { background:#fef2f2; }

    /* Table */
    .data-table { width:100%; border-collapse:collapse; font-size:13px; }
    .data-table thead th {
      padding: 9px 16px;
      text-align: left;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .06em;
      color: #94a3b8;
      background: #f8fafc;
      border-bottom: 1px solid #e2e8f0;
    }
    .data-table thead th:first-child { border-radius: 0; }
    .data-table tbody tr { border-bottom: 1px solid #f1f5f9; }
    .data-table tbody tr:hover { background: #f8fafc; }
    .data-table tbody td { padding: 10px 16px; vertical-align: middle; color: #1e293b; }
    .data-table tbody tr:last-child { border-bottom: none; }

    /* Badges */
    .badge { display:inline-flex; align-items:center; padding:2px 8px; border-radius:20px; font-size:11px; font-weight:600; }
    .badge-green  { background:#d1fae5; color:#065f46; }
    .badge-amber  { background:#fef3c7; color:#92400e; }
    .badge-red    { background:#fee2e2; color:#991b1b; }
    .badge-blue   { background:#dbeafe; color:#1e40af; }
    .badge-slate  { background:#f1f5f9; color:#475569; }
    .badge-violet { background:#ede9fe; color:#5b21b6; }

    /* Flash */
    .flash-ok  { display:flex; align-items:center; gap:8px; background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; border-radius:6px; padding:10px 14px; font-size:13px; }
    .flash-err { display:flex; align-items:center; gap:8px; background:#fef2f2; border:1px solid #fecaca; color:#dc2626; border-radius:6px; padding:10px 14px; font-size:13px; }

    /* Pagination */
    .pg-btn { display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border:1px solid #e2e8f0; border-radius:5px; font-size:13px; font-weight:500; color:#475569; text-decoration:none; }
    .pg-btn:hover { background:#f1f5f9; }
    .pg-btn.active { background:#2563eb; border-color:#2563eb; color:#fff; }

    /* Alert pill */
    .alert-pill { display:inline-flex; align-items:center; gap:5px; padding:5px 10px; background:#fffbeb; border:1px solid #fde68a; border-radius:20px; font-size:12px; font-weight:500; color:#92400e; }
  </style>
</head>
<body class="h-full">
<div id="layout" class="flex h-screen overflow-hidden">
