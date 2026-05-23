<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} — Flow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" href="/favicon.ico">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                        accent: '#14b8a6',
                        bg: '#18181b',
                        card: '#23232a',
                        kpi: '#26263a',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-bg text-white min-h-screen dark">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-card border-r border-zinc-800 flex flex-col py-6 px-4 hidden md:flex">
            <div class="mb-8 flex items-center gap-2">
                <span class="text-2xl font-bold tracking-tight text-primary">Flow</span>
            </div>
            <nav class="flex-1 space-y-2">
                <a href="/dashboard" class="block py-2 px-3 rounded hover:bg-zinc-700 transition">Dashboard</a>
                <a href="/clients" class="block py-2 px-3 rounded hover:bg-zinc-700 transition">Clients</a>
                <a href="/services" class="block py-2 px-3 rounded hover:bg-zinc-700 transition">Services</a>
                <a href="/expenses" class="block py-2 px-3 rounded hover:bg-zinc-700 transition">Dépenses</a>
                <a href="/ai" class="block py-2 px-3 rounded hover:bg-zinc-700 transition">IA</a>
            </nav>
            <form method="POST" action="/logout" class="mt-8">
                @csrf
                <button type="submit" class="w-full py-2 px-3 rounded bg-primary hover:bg-accent transition">Déconnexion</button>
            </form>
        </aside>
        <!-- Main -->
        <div class="flex-1 flex flex-col">
            <!-- Topbar -->
            <header class="flex items-center justify-between px-4 py-3 bg-card border-b border-zinc-800 shadow-sm">
                <div class="flex items-center gap-2 md:hidden">
                    <button id="sidebar-toggle" class="text-primary focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <span class="text-xl font-bold tracking-tight text-primary">Flow</span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-zinc-400">{{ $user->name ?? '' }}</span>
                    <button id="dark-toggle" class="text-zinc-400 hover:text-primary transition">
                        <svg id="icon-dark" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m8.66-13.66l-.71.71M4.05 19.95l-.71.71M21 12h-1M4 12H3m16.66 5.66l-.71-.71M4.05 4.05l-.71-.71" /></svg>
                    </button>
                </div>
            </header>
            <!-- Content -->
            <main class="flex-1 p-6 bg-bg">
                @yield('content')
            </main>
        </div>
    </div>
    <script>
        // Dark mode toggle
        document.getElementById('dark-toggle').onclick = function() {
            document.documentElement.classList.toggle('dark');
        };
        // Sidebar mobile toggle (optionnel)
    </script>
</body>
</html>
