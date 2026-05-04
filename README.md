 # Flow – Tableau de bord financier
 
 Application web de gestion financière légère pour TPE/PME.
 PHP 8.4 custom MVC · Tailwind CSS (CDN Play) · Vue.js 3 (CDN) · Chart.js 4 · MySQL.

 ---

 ## Stack technique

 | Couche | Technologie |
 |--------|-------------|
 | Backend | PHP 8.4 (pas de framework) |
 | Routing | `index.php` → `controllers/` |
 | Base de données | MySQL (PDO) |
 | Frontend CSS | Tailwind CSS Play CDN |
 | Interactivité | Vue.js 3 CDN (`createApp` / `ref`) |
 | Graphiques | Chart.js 4.4 CDN |
 | Auth | Google OAuth 2.0 (domaine restreint) |
 | Icônes | Material Icons Round CDN |
 | Déploiement | cPanel / o2switch |

 ---

 ## Architecture

 ```
 flow/
 ├── index.php                    # Routeur principal (switch sur $path)
 ├── .env                         # Variables d'environnement (non versionné)
 ├── .htaccess                    # Sécurité Apache + routing vers index.php
 ├── config/
 │   ├── app.php                  # Constantes globales (APP_URL, etc.)
 │   ├── auth.php                 # Google OAuth
 │   └── database.php             # Connexion PDO
 ├── controllers/
 │   ├── AuthController.php       # Login / callback / logout Google OAuth
 │   ├── DashboardController.php  # KPIs + graphiques accueil
 │   ├── TiersController.php      # CRUD clients
 │   ├── InvoicesController.php   # CRUD factures + mark-paid
 │   ├── PaymentsController.php   # CRUD paiements
 │   ├── ExpensesController.php   # CRUD dépenses
 │   ├── ForecastController.php   # Prévisions
 │   └── ExportController.php     # Exports CSV
 ├── models/                      # Modèles PDO
 ├── views/
 │   ├── partials/
 │   │   ├── header.php           # CDN (Tailwind, Vue, Chart.js), ouvre <body>
 │   │   ├── sidebar.php          # Navigation latérale sombre (slate-900)
 │   │   └── footer.php           # Sidebar JS, Chart.js defaults, ferme </html>
 │   ├── login.php
 │   ├── dashboard.php
 │   ├── tiers.php / tiers_detail.php
 │   ├── invoices.php
 │   ├── payments.php
 │   ├── expenses.php
 │   └── forecast.php
 ├── services/                    # Services métier (KPI, forecast, risk scoring)
 ├── database/
 │   ├── schema.sql               # Schéma initial complet
 │   ├── seed.php                 # Données de démonstration
 │   └── migrations/
 │       ├── 001_create_expenses.sql
 │       └── 002_remove_dolibarr.sql
 └── cron/
		 └── kpi_recalc.php           # Recalcul KPI + scores de risque
 ```

 ---

 ## Installation

 ### 1. Déposer les fichiers

 Sur cPanel : déposer le contenu dans `public_html/` ou dans un sous-dossier.

 ### 2. Créer la base de données

 ```sql
 CREATE DATABASE flow_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
 ```

 Importer le schéma :

 ```bash
 mysql -u root -p flow_db < database/schema.sql
 ```

 Migration si mise à jour depuis une ancienne version :

 ```bash
 mysql -u root -p flow_db < database/migrations/002_remove_dolibarr.sql
 ```

 ### 3. Configurer l'environnement

 Créer `.env` à la racine :

 ```env
 APP_URL=https://flow.exemple.com
 APP_ENV=production

 DB_HOST=localhost
 DB_NAME=flow_db
 DB_USER=mon_user
 DB_PASS=mon_mot_de_passe

 GOOGLE_CLIENT_ID=xxxx.apps.googleusercontent.com
 GOOGLE_CLIENT_SECRET=GOCSPX-xxxx
 ALLOWED_DOMAIN=groupe-speed.cloud
 AUTHORIZED_USERS=user1@groupe-speed.cloud,user2@groupe-speed.cloud
 ```

 ### 4. Google OAuth 2.0

 1. [Google Cloud Console](https://console.cloud.google.com/) → **APIs & Services** → **Identifiants**
 2. Créer un **ID client OAuth 2.0** → Application web
 3. URI de redirection autorisée : `https://votre-domaine.com/auth/google/callback`
 4. Copier le Client ID et Client Secret dans `.env`

 Seuls les comptes `@groupe-speed.cloud` listés dans `AUTHORIZED_USERS` peuvent se connecter.

 ### 5. Données de démonstration (optionnel)

 ```bash
 php database/seed.php
 ```

 ---

 ## Comment ça fonctionne

 ### Routing

 `index.php` parse `$_SERVER['REQUEST_URI']`, extrait le chemin et route vers le contrôleur/méthode :

 ```
 GET  /              → DashboardController::index()
 GET  /tiers         → TiersController::index()
 POST /tiers/store   → TiersController::store()
 POST /tiers/update/5→ TiersController::update(5)
 POST /tiers/delete/5→ TiersController::destroy(5)
 GET  /invoices      → InvoicesController::index()
 POST /invoices/store→ InvoicesController::store()
 POST /invoices/pay/3→ InvoicesController::markPaid(3)
 ...
 ```

 Toutes les actions POST valident un token CSRF via `validateCsrf()`.

 ### Frontend

 Chaque vue PHP inclut `header.php` (CDN, ouvre `<body>`) et `footer.php` (ferme, JS sidebar, Chart.js defaults).
 Vue.js 3 gère l'interactivité : boutons toggle, modales de confirmation.

 ```js
 const { createApp, ref } = Vue;
 createApp({
	 setup() {
		 const showAdd       = ref(false);
		 const confirmDelete = ref(null);
		 const csrf          = '<?= $csrf ?>';
		 return { showAdd, confirmDelete, csrf };
	 }
 }).mount('#page-app');
 ```

 ### Sécurité

 - HTTPS imposé par `.htaccess`
 - Token CSRF sur tous les formulaires POST
 - Échappement systématique `htmlspecialchars()` / `ENT_QUOTES`
 - Requêtes préparées PDO (pas d'interpolation SQL)
 - Auth Google uniquement (pas de mot de passe stocké)
 - Restriction domaine + whitelist email

 ---

 ## Fonctionnalités

 ### Tableau de bord
 KPIs temps réel : CA mensuel/annuel, run-rate, résultat, marge nette, cash, factures en retard.
 Graphique CA 12 mois, donut dépenses, top clients, top services.

 ### Tiers (clients)
 Liste paginée, recherche, filtre par risque. CRUD complet (ajout, modif, suppression).
 Fiche détail : historique CA, alertes de risque, factures et paiements liés.

 ### Factures
 Création manuelle (référence auto `MAN-XXXXXXXX`).
 Statuts : Brouillon / Validée / Payée / Abandonnée.
 Marquer payée en un clic (crée automatiquement un paiement).

 ### Paiements
 Enregistrement manuel (tiers, montant, date, mode, libellé).
 KPIs par mode, graphique mensuel, historique récent (50 lignes).

 ### Dépenses
 CRUD avec récurrence (mensuelle / annuelle / ponctuelle).
 Comparatif revenus vs dépenses vs profit mois et année.

 ### Prévisions
 `ForecastService` :
 - Historique 18 mois + moyennes mobiles MA3/MA6
 - Régression linéaire (moindres carrés)
 - Détection récurrences (mensuel / trimestriel / annuel)
 - Projections 3/6/12 mois (CA brut + net après dépenses)
 - Score santé financière 0-100

 | Intervalle moyen | Périodicité |
 |---|---|
 | 20 – 50 j | Mensuelle |
 | 75 – 115 j | Trimestrielle |
 | 300 – 420 j | Annuelle |

 ### Exports
 CSV pour factures, paiements, dépenses via `/export/csv?type=xxx`.

 ---

 ## Tâche Cron

 ```cron
 30 2 * * * php /home/user/public_html/flow/cron/kpi_recalc.php >> /var/log/flow_kpi.log 2>&1
 ```

 Sur cPanel : **Tâches Cron** → ajouter la commande ci-dessus.

 ---

 ## Déploiement cPanel (o2switch)

 1. Déposer les fichiers via FTP ou gestionnaire de fichiers cPanel
 2. Créer la base MySQL + l'utilisateur dans **Bases de données MySQL**
 3. Importer `database/schema.sql` via phpMyAdmin
 4. Créer `.env` avec les bonnes valeurs
 5. Vérifier que `.htaccess` est présent à la racine
 6. Tester `https://votre-domaine.com/` → redirige vers login Google
- Protection CSRF sur tous les formulaires POST
- XSS : `htmlspecialchars()` systématique
- Sessions sécurisées (httponly, samesite=Strict, régénération)
- `.env` inaccessible depuis le web (`.htaccess`)
- Requêtes SQL via PDO préparé uniquement

## Compatibilité

- PHP 8.5+
- MySQL 5.7+ / MariaDB 10.3+
- Apache 2.4+ avec mod_rewrite
- Hébergement cPanel mutualisé compatible
