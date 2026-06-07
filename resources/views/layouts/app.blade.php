<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Flow') — Gestion Financière</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg: #0a0a0a;
            --surface: #111111;
            --surface-2: #1a1a1a;
            --border: #1e1e1e;
            --border-2: #2a2a2a;
            --text: #ffffff;
            --text-2: #888888;
            --text-3: #555555;
            --accent: #6366f1;
            --accent-bg: rgba(99, 102, 241, 0.12);
            --green: #10b981;
            --red: #ef4444;
            --yellow: #f59e0b;
        }

        * { box-sizing: border-box; }

        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, 'Inter', 'Segoe UI', sans-serif;
            margin: 0;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 220px;
            background: var(--bg);
            border-right: 1px solid var(--surface-2);
            display: flex;
            flex-direction: column;
            padding: 20px 12px;
            position: fixed;
            height: 100%;
            z-index: 30;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 4px;
            margin-bottom: 28px;
        }

        .sidebar-logo-icon {
            width: 30px;
            height: 30px;
            background: var(--accent);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-logo-text {
            font-size: 17px;
            font-weight: 700;
            letter-spacing: -0.03em;
            color: var(--text);
        }

        .nav-section-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-3);
            padding: 0 8px;
            margin-bottom: 6px;
            margin-top: 16px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 9px 10px;
            border-radius: 8px;
            color: var(--text-2);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: background 0.15s, color 0.15s;
            border-left: 2px solid transparent;
            margin-bottom: 1px;
        }

        .sidebar-link:hover {
            background: var(--surface-2);
            color: var(--text);
        }

        .sidebar-link.active {
            background: var(--accent-bg);
            color: var(--accent);
            border-left-color: var(--accent);
        }

        .sidebar-link svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            opacity: 0.7;
        }

        .sidebar-link.active svg,
        .sidebar-link:hover svg {
            opacity: 1;
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 6px;
            margin-bottom: 6px;
        }

        .sidebar-avatar {
            width: 30px;
            height: 30px;
            background: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            flex-shrink: 0;
        }

        .sidebar-user-info { flex: 1; min-width: 0; }
        .sidebar-user-name { font-size: 13px; font-weight: 500; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-user-email { font-size: 11px; color: var(--text-3); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .sidebar-logout {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px 10px;
            border-radius: 8px;
            color: var(--text-3);
            font-size: 13px;
            font-weight: 500;
            background: none;
            border: none;
            width: 100%;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
            text-align: left;
        }

        .sidebar-logout:hover {
            background: rgba(239, 68, 68, 0.1);
            color: var(--red);
        }

        /* Topbar */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            height: 52px;
            background: rgba(10, 10, 10, 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
        }

        .topbar-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
            letter-spacing: -0.01em;
        }

        .topbar-date {
            font-size: 12px;
            color: var(--text-3);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Main layout */
        .main-layout {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 220px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .page-content {
            flex: 1;
            padding: 24px;
        }

        /* Flash messages */
        .flash-container { padding: 16px 24px 0; }

        .flash {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .flash-success {
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .flash-error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--red);
        }

        .flash svg { width: 15px; height: 15px; flex-shrink: 0; }

        /* Cards */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            transition: border-color 0.15s;
        }

        .card:hover { border-color: var(--border-2); }

        .card-flush {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            transition: border-color 0.15s;
        }

        /* KPI cards */
        .kpi-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .kpi-card:hover {
            border-color: var(--border-2);
            box-shadow: 0 4px 24px rgba(0,0,0,0.3);
        }

        .kpi-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-3);
            margin-bottom: 12px;
        }

        .kpi-value {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.03em;
            color: var(--text);
            line-height: 1;
        }

        .kpi-sub {
            font-size: 12px;
            color: var(--text-3);
            margin-top: 8px;
        }

        .kpi-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .kpi-icon svg { width: 15px; height: 15px; }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s;
            border: none;
            white-space: nowrap;
        }

        .btn svg { width: 14px; height: 14px; }

        .btn-primary {
            background: var(--accent);
            color: white;
        }

        .btn-primary:hover {
            background: #5254cc;
            box-shadow: 0 0 16px rgba(99, 102, 241, 0.35);
        }

        .btn-secondary {
            background: var(--surface-2);
            color: var(--text-2);
            border: 1px solid var(--border-2);
        }

        .btn-secondary:hover {
            background: #222222;
            color: var(--text);
        }

        .btn-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--red);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.18);
        }

        .btn-ghost {
            background: transparent;
            color: var(--text-3);
            padding: 6px 10px;
            font-size: 12px;
        }

        .btn-ghost:hover {
            background: var(--surface-2);
            color: var(--text);
        }

        .btn-ghost-red {
            background: transparent;
            color: var(--text-3);
            padding: 6px 10px;
            font-size: 12px;
        }

        .btn-ghost-red:hover {
            background: rgba(239, 68, 68, 0.1);
            color: var(--red);
        }

        /* Form inputs */
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-3);
            margin-bottom: 6px;
            letter-spacing: 0.01em;
        }

        .form-input {
            width: 100%;
            background: var(--surface-2);
            border: 1px solid var(--border-2);
            border-radius: 8px;
            padding: 9px 12px;
            color: var(--text);
            font-size: 13px;
            font-family: inherit;
            transition: border-color 0.15s, box-shadow 0.15s;
            outline: none;
        }

        .form-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .form-input::placeholder { color: var(--text-3); }

        select.form-input {
            -webkit-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23555555'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 14px;
            padding-right: 34px;
            cursor: pointer;
        }

        select.form-input option {
            background: var(--surface-2);
            color: var(--text);
        }

        /* Tables */
        .data-table { width: 100%; border-collapse: collapse; font-size: 13px; }

        .data-table thead th {
            text-align: left;
            padding: 12px 16px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-3);
            background: rgba(255,255,255,0.02);
            border-bottom: 1px solid var(--border);
        }

        .data-table th.text-right { text-align: right; }
        .data-table th.text-center { text-align: center; }

        .data-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.1s;
        }

        .data-table tbody tr:last-child { border-bottom: none; }
        .data-table tbody tr:hover { background: #141414; }

        .data-table td {
            padding: 12px 16px;
            color: var(--text-2);
            vertical-align: middle;
        }

        .data-table td.text-right { text-align: right; }
        .data-table td.text-center { text-align: center; }

        .data-table tfoot tr { border-top: 1px solid var(--border-2); }

        .data-table tfoot td {
            padding: 12px 16px;
            font-weight: 600;
            color: var(--text);
            background: rgba(255,255,255,0.02);
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-green { background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid rgba(16,185,129,0.2); }
        .badge-red { background: rgba(239,68,68,0.1); color: var(--red); border: 1px solid rgba(239,68,68,0.2); }
        .badge-yellow { background: rgba(245,158,11,0.1); color: #f59e0b; border: 1px solid rgba(245,158,11,0.2); }
        .badge-indigo { background: var(--accent-bg); color: var(--accent); border: 1px solid rgba(99,102,241,0.25); }
        .badge-muted { background: rgba(255,255,255,0.05); color: var(--text-3); border: 1px solid var(--border-2); }
        .badge-blue { background: rgba(59,130,246,0.1); color: #60a5fa; border: 1px solid rgba(59,130,246,0.2); }
        .badge-purple { background: rgba(139,92,246,0.1); color: #a78bfa; border: 1px solid rgba(139,92,246,0.2); }
        .badge-cyan { background: rgba(6,182,212,0.1); color: #22d3ee; border: 1px solid rgba(6,182,212,0.2); }

        /* Dot color indicator */
        .project-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* Page header */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .page-header-left { display: flex; align-items: center; gap: 12px; }

        .page-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.02em;
        }

        .page-subtitle { font-size: 13px; color: var(--text-3); margin-top: 2px; }

        /* Count badge */
        .count-badge {
            background: var(--surface-2);
            border: 1px solid var(--border-2);
            color: var(--text-3);
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
        }

        /* Card headers */
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
        }

        .card-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
        }

        .card-subtitle { font-size: 12px; color: var(--text-3); margin-top: 2px; }

        /* Section label */
        .section-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-3);
            margin-bottom: 12px;
        }

        /* Trend indicator */
        .trend-up { color: #10b981; font-size: 12px; }
        .trend-down { color: var(--red); font-size: 12px; }

        /* Amount inputs */
        .amount-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .amount-input {
            width: 100%;
            background: var(--surface-2);
            border: 1px solid var(--border-2);
            border-radius: 8px;
            padding: 11px 40px 11px 12px;
            color: var(--text);
            font-size: 16px;
            font-weight: 600;
            font-family: inherit;
            transition: border-color 0.15s, box-shadow 0.15s;
            outline: none;
        }

        .amount-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .amount-suffix {
            position: absolute;
            right: 12px;
            color: var(--text-3);
            font-size: 14px;
            pointer-events: none;
        }

        /* Toggle switch */
        .toggle-wrap { display: flex; align-items: center; gap: 8px; cursor: pointer; }

        .toggle {
            position: relative;
            width: 36px;
            height: 20px;
            flex-shrink: 0;
        }

        .toggle input { opacity: 0; width: 0; height: 0; }

        .toggle-track {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: var(--surface-2);
            border: 1px solid var(--border-2);
            border-radius: 20px;
            transition: all 0.2s;
        }

        .toggle-thumb {
            position: absolute;
            height: 14px;
            width: 14px;
            left: 3px;
            bottom: 3px;
            background: var(--text-3);
            border-radius: 50%;
            transition: all 0.2s;
        }

        .toggle input:checked ~ .toggle-track {
            background: var(--accent-bg);
            border-color: var(--accent);
        }

        .toggle input:checked ~ .toggle-thumb {
            transform: translateX(16px);
            background: var(--accent);
        }

        /* Color picker */
        .color-swatch {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.15s;
            position: relative;
        }

        .color-swatch:hover { transform: scale(1.1); }

        .color-swatch.selected {
            border-color: white;
            box-shadow: 0 0 0 2px var(--bg);
        }

        /* Year selector */
        .year-nav {
            display: flex;
            align-items: center;
            gap: 0;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
        }

        .year-nav-btn {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-3);
            cursor: pointer;
            background: none;
            border: none;
            transition: background 0.15s, color 0.15s;
        }

        .year-nav-btn:hover { background: var(--surface-2); color: var(--text); }

        .year-nav-value {
            padding: 0 12px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            border-left: 1px solid var(--border);
            border-right: 1px solid var(--border);
            height: 32px;
            display: flex;
            align-items: center;
        }

        /* Misc */
        .text-muted { color: var(--text-3); }
        .text-secondary { color: var(--text-2); }
        .text-green { color: var(--green); }
        .text-red { color: var(--red); }
        .text-accent { color: var(--accent); }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border-2); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-3); }

        /* Chart area */
        .chart-wrap { height: 220px; position: relative; }

        /* Mobile */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.2s; }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .topbar { padding: 0 16px; }
            .page-content { padding: 16px; }
        }
    </style>
</head>
<body>
<div class="main-layout">
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <span class="sidebar-logo-text">Flow</span>
        </div>

        <nav>
            <div class="nav-section-label">Navigation</div>
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10-2a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z"/></svg>
                Dashboard
            </a>
            <a href="{{ route('projects.index') }}" class="sidebar-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                Projets
            </a>
            <a href="{{ route('revenues.index') }}" class="sidebar-link {{ request()->routeIs('revenues.*') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Revenus
            </a>
            <a href="{{ route('expenses.index') }}" class="sidebar-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Dépenses
            </a>
            <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Rapport
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar">
                    {{ substr(Auth::user()?->name ?? 'U', 0, 1) }}
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ Auth::user()?->name }}</div>
                    <div class="sidebar-user-email">{{ Auth::user()?->email }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-logout">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Déconnexion
                </button>
            </form>
        </div>
    </aside>

    <!-- Mobile overlay -->
    <div id="sidebar-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:29;"></div>

    <!-- Main -->
    <div class="main-content">
        <header class="topbar">
            <div style="display:flex;align-items:center;gap:10px;">
                <button id="sidebar-toggle" style="display:none;background:none;border:none;color:var(--text-2);cursor:pointer;padding:4px;" class="mobile-menu-btn">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <span class="topbar-title">@yield('page-title', 'Dashboard')</span>
            </div>
            <div class="topbar-date">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                {{ now()->isoFormat('D MMM YYYY') }}
            </div>
        </header>

        @if(session('success') || session('error') || $errors->any())
        <div class="flash-container">
            @if(session('success'))
                <div class="flash flash-success">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flash flash-error">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="flash flash-error" style="flex-direction:column;align-items:flex-start;gap:4px;">
                    @foreach($errors->all() as $error)
                        <span style="display:flex;align-items:center;gap:8px;">
                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            {{ $error }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
        @endif

        <main class="page-content">
            @yield('content')
        </main>
    </div>
</div>

<script>
    const toggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (window.innerWidth < 768) {
        toggle.style.display = 'block';
    }
    toggle?.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
    });
    overlay?.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.style.display = 'none';
    });
</script>
@stack('scripts')
</body>
</html>
