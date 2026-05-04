<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Flow – Pilotage financier</title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Manrope', 'system-ui', 'sans-serif'],
            display: ['Space Grotesk', 'Manrope', 'sans-serif']
          },
          colors: {
            brand: { 50:'#eff6ff', 100:'#dbeafe', 500:'#3b82f6', 600:'#2563eb', 700:'#1d4ed8' }
          }
        }
      }
    }
  </script>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap">

  <!-- Material Icons Round -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons+Round">

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

  <!-- Vue.js 3 -->
  <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>

  <style>
    :root {
      --bg-0: #f5f7fb;
      --bg-1: #eef3ff;
      --ink-1: #0f172a;
      --ink-2: #334155;
      --line-1: #dbe5f2;
      --accent-1: #2563eb;
      --accent-2: #0ea5e9;
    }
    body {
      font-family: 'Manrope', system-ui, sans-serif;
      color: var(--ink-1);
      background:
        radial-gradient(1200px 420px at 85% -10%, rgba(37,99,235,.13), transparent 60%),
        radial-gradient(900px 360px at 0% 0%, rgba(14,165,233,.10), transparent 55%),
        linear-gradient(180deg, var(--bg-1), var(--bg-0));
    }
    h1, h2, h3, .font-display { font-family: 'Space Grotesk', 'Manrope', sans-serif; letter-spacing: -0.02em; }
    [v-cloak] { display: none; }
    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    #sidebar { transition: transform .25s cubic-bezier(.4,0,.2,1); }
    #sidebar-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,.45); z-index: 25;
    }
    #sidebar-overlay.active { display: block; }
    @media (max-width: 1024px) {
      #sidebar { transform: translateX(-100%); position: fixed; }
      #sidebar.open { transform: translateX(0); }
      #main-wrap { margin-left: 0 !important; }
    }

    header.bg-white.border-b.border-slate-200 {
      background: rgba(255,255,255,.86) !important;
      backdrop-filter: saturate(1.3) blur(10px);
      border-color: var(--line-1) !important;
      box-shadow: 0 1px 0 rgba(15,23,42,.02);
    }
    .bg-white.border.border-slate-200.rounded-xl {
      border-color: var(--line-1) !important;
      box-shadow: 0 12px 28px rgba(15,23,42,.06), 0 2px 8px rgba(15,23,42,.03);
    }
    .bg-slate-50 {
      background: linear-gradient(180deg, #f8fafc, #f3f6fb) !important;
    }
    .text-slate-900 { color: #0b1220 !important; }
    .text-slate-700 { color: #25364d !important; }
    .text-slate-600 { color: #3e5573 !important; }
    .border-slate-100 { border-color: #e8eef7 !important; }
    .border-slate-200 { border-color: var(--line-1) !important; }
  </style>
</head>
<body class="h-full bg-slate-50" style="font-family:'Manrope',system-ui,sans-serif;">
<div id="layout" class="flex h-screen overflow-hidden">
