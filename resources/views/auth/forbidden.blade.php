<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès refusé — Flow</title>
    <style>
        :root {
            --bg: #0a0a0a;
            --surface: #111111;
            --border: #1e1e1e;
            --border-2: #2a2a2a;
            --text: #ffffff;
            --text-2: #888888;
            --text-3: #555555;
            --accent: #6366f1;
            --red: #ef4444;
            --yellow: #f59e0b;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, 'Inter', 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bg-glow {
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse 60% 40% at 50% 0%, rgba(239,68,68,0.06), transparent);
            pointer-events: none;
        }
        .wrap {
            width: 100%;
            max-width: 480px;
            padding: 24px;
            position: relative;
            z-index: 1;
        }
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 36px;
        }
        .logo-icon {
            width: 36px;
            height: 36px;
            background: var(--accent);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-name {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.04em;
        }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 36px 32px;
            text-align: center;
        }
        .icon-wrap {
            width: 56px;
            height: 56px;
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        h1 {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 13px;
            color: var(--text-2);
            line-height: 1.6;
            margin-bottom: 28px;
        }
        .divider {
            height: 1px;
            background: var(--border);
            margin: 24px 0;
        }
        .ticket-section {
            background: rgba(99,102,241,0.05);
            border: 1px solid rgba(99,102,241,0.15);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            text-align: left;
        }
        .ticket-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-3);
            margin-bottom: 10px;
        }
        .ticket-text {
            font-size: 13px;
            color: var(--text-2);
            line-height: 1.6;
            margin-bottom: 14px;
        }
        .btn-ticket {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background: var(--accent);
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            transition: opacity 0.15s;
            box-shadow: 0 0 20px rgba(99,102,241,0.2);
        }
        .btn-ticket:hover { opacity: 0.85; }
        .btn-back {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background: transparent;
            border: 1px solid var(--border-2);
            border-radius: 10px;
            padding: 11px 20px;
            color: var(--text-3);
            font-size: 13px;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            transition: color 0.15s, border-color 0.15s;
        }
        .btn-back:hover { color: var(--text); border-color: #444; }
        .email-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 6px;
            padding: 3px 10px;
            font-size: 12px;
            color: #f87171;
            font-family: monospace;
            margin: 4px 0;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 11px;
            color: var(--text-3);
        }
    </style>
</head>
<body>
<div class="bg-glow"></div>
<div class="wrap">
    <div class="logo">
        <div class="logo-icon">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
        </div>
        <span class="logo-name">Flow</span>
    </div>

    <div class="card">
        <div class="icon-wrap">
            <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <h1>Accès non autorisé</h1>
        <p class="subtitle">
            Votre compte ne dispose pas des habilitations nécessaires pour accéder à cette application.
            @if(session('blocked_email'))
                <br><br>
                <span class="email-chip">
                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    {{ session('blocked_email') }}
                </span>
            @endif
        </p>

        <div class="ticket-section">
            <div class="ticket-label">Demande d'accès</div>
            <p class="ticket-text">
                Pour obtenir les habilitations nécessaires, veuillez ouvrir un ticket sur le portail de support Syselia en précisant votre email et l'application concernée.
            </p>
            <a href="https://servicedesk.syselia.net" target="_blank" rel="noopener" class="btn-ticket">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
                Ouvrir un ticket sur servicedesk.syselia.net
            </a>
        </div>

        <a href="/login" class="btn-back">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Retour à la connexion
        </a>
    </div>

    <div class="footer">Flow — Gestion financière interne · Groupe Speed</div>
</div>
</body>
</html>
