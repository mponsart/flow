# Flow – Tableau de bord stratégique

Application web PHP de gestion stratégique pour organisation à but non lucratif.
Synchronisation Dolibarr, analyse financière, scoring de risque clients.

## Architecture

```
flow/
├── index.php              # Routeur principal
├── .env.example           # Variables d'environnement (template)
├── .htaccess              # Sécurité Apache + routing
├── config/                # Configuration (app, database, auth)
├── controllers/           # Contrôleurs MVC
├── models/                # Modèles PDO
├── views/                 # Vues PHP + Chart.js
├── services/              # Services métier
├── database/              # Schéma SQL + seeder
└── cron/                  # Scripts cron
```

## Installation

### 1. Cloner / déposer les fichiers

```bash
# Sur cPanel : déposer dans public_html/flow ou à la racine
```

### 2. Configurer l'environnement

```bash
cp .env.example .env
# Éditer .env avec vos paramètres
```

### 3. Créer la base de données

Dans phpMyAdmin ou via CLI :

```sql
CREATE DATABASE flow_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Puis importer le schéma :

```bash
mysql -u root -p flow_db < database/schema.sql
```

### 4. Générer les données de démonstration (optionnel)

```bash
php database/seed.php
```

### 5. Configuration Apache

Le fichier `.htaccess` est déjà configuré. Assurez-vous que `mod_rewrite` est activé.

## Configuration `.env`

| Variable | Description |
|---|---|
| `APP_URL` | URL de base de l'application (sans slash final) |
| `DB_HOST` | Hôte MySQL |
| `DB_NAME` | Nom de la base de données |
| `DB_USER` | Utilisateur MySQL |
| `DB_PASS` | Mot de passe MySQL |
| `GOOGLE_CLIENT_ID` | Client ID OAuth Google |
| `GOOGLE_CLIENT_SECRET` | Client Secret OAuth Google |
| `AUTHORIZED_USERS` | Emails autorisés séparés par virgule |
| `DOLIBARR_URL` | URL de l'instance Dolibarr |
| `DOLIBARR_API_KEY` | Clé API Dolibarr (REST) |

## Configuration Google OAuth

1. Aller sur [Google Cloud Console](https://console.cloud.google.com/)
2. Créer un projet ou en sélectionner un existant
3. Activer l'API **Google+ API** (ou **Google Identity**)
4. Créer des identifiants → **ID client OAuth 2.0** → Application web
5. Ajouter l'URI de redirection autorisée : `https://votre-domaine.com/auth/google/callback`
6. Copier Client ID et Client Secret dans `.env`

**Restriction d'accès :**
- Seuls les emails `@groupe-speed.cloud` sont acceptés
- En plus, l'email doit figurer dans `AUTHORIZED_USERS`

## Configuration API Dolibarr

1. Dans Dolibarr : **Configuration → API/Services REST**
2. Activer l'API REST
3. Générer une clé API pour l'utilisateur administrateur
4. Renseigner `DOLIBARR_URL` et `DOLIBARR_API_KEY` dans `.env`

La synchronisation récupère :
- Tiers (clients/fournisseurs)
- Produits et services
- Factures et leurs lignes
- Paiements

## Tâches Cron

Ajouter dans crontab (`crontab -e`) :

```cron
# Synchronisation Dolibarr toutes les heures
0 * * * * php /chemin/vers/flow/cron/sync.php >> /var/log/flow_sync.log 2>&1

# Recalcul KPI et scores de risque chaque nuit à 2h30
30 2 * * * php /chemin/vers/flow/cron/kpi_recalc.php >> /var/log/flow_kpi.log 2>&1
```

Sur cPanel : **Tâches Cron** dans le panneau de contrôle.

## Fonctionnalités

### Tableau de bord
- KPIs : CA mensuel, annuel, croissance, panier moyen
- Compteurs factures (payées, impayées, en retard)
- Graphiques : évolution CA 12 mois, répartition produits, top 10 tiers/produits

### Tiers
- Liste paginée avec recherche et filtre par niveau de risque
- Fiche détaillée : CA, historique, paiements, alertes, score de risque

### Paiements
- Répartition par mode (CB, virement, chèque, espèces)
- Distribution de fréquence par client
- Évolution mensuelle des encaissements

### Prévisions
- Moyennes mobiles 3 et 6 mois
- Projections 3 / 6 / 12 mois (régression linéaire)
- Score de santé financière
- Indicateur de tendance

### Synchronisation
- Statut par entité et date de dernière sync
- Journal des opérations
- Synchronisation forcée manuelle

### Exports
- CSV : factures, tiers, paiements
- PDF : rapport complet (HTML → impression navigateur)

## Sécurité

- Authentification Google OAuth 2.0 uniquement
- Validation domaine `@groupe-speed.cloud` + whitelist
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
