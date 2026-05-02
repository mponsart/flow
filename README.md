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

## Fichiers locaux non versionnés

Les dossiers et fichiers suivants ne doivent pas être commités :

- `.env` : configuration et secrets propres à chaque environnement
- `vendor/` : dépendances installées localement si Composer est utilisé
- `storage/` : logs, cache, sessions et fichiers générés par l'application

Ils sont ignorés par `.gitignore`. Sur un serveur, créez ou laissez l'application créer `storage/` avec les permissions d'écriture nécessaires.

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
| `ALLOWED_DOMAIN` | Domaine Google Workspace autorisé |
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

`DOLIBARR_URL` peut être l'URL racine de Dolibarr (`https://dolibarr.example.com`) ou l'URL API complète (`https://dolibarr.example.com/api/index.php`). L'application normalise automatiquement le chemin API.

Les appels API envoient un `User-Agent` explicite (`FlowSync/1.0`) afin d'éviter les blocages cPanel/o2switch de type `Security_Rule = "emptyua"`.

La synchronisation récupère :
- Tiers (clients/fournisseurs)
- Services Dolibarr (`/products?type=1`)
- Factures et leurs lignes
- Paiements

La synchronisation relit toutes les pages disponibles côté Dolibarr et met à jour la base locale par upsert. Les lignes de factures sont remplacées à chaque resynchronisation de la facture afin d'éviter les doublons dans les indicateurs. Les services sont récupérés via l'endpoint Dolibarr `/products` avec `type=1`. Si cet endpoint est refusé par Dolibarr, la synchronisation continue et l'application crée les services nécessaires à partir des lignes de factures lorsque Dolibarr fournit `fk_product`.

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

Le moteur de prévision (`ForecastService`) combine deux approches complémentaires.

#### 1. Historique et moyennes mobiles
Les 18 derniers mois de factures **payées** (statut 2) sont chargés. Deux moyennes mobiles sont calculées sur ces revenus réels :
- **MA3** : moyenne glissante sur 3 mois (réactivité)
- **MA6** : moyenne glissante sur 6 mois (lissage du bruit)

#### 2. Régression linéaire
Une droite de tendance est ajustée sur l'historique (moindres carrés). Elle est extrapolée sur 3, 6 ou 12 mois pour produire une projection *linéaire*. Si les revenus sont tous nuls (base vide), la projection linéaire vaut 0.

#### 3. Détection des factures récurrentes
Les factures payées sont regroupées par couple **(tiers × libellé produit)**. Pour chaque groupe, les intervalles en jours entre factures consécutives sont calculés, puis classifiés :

| Intervalle moyen | Périodicité détectée |
|---|---|
| 20 – 50 jours | Mensuelle |
| 75 – 115 jours | Trimestrielle |
| 300 – 420 jours | Annuelle |
| Hors plages | Irrégulière (ignorée) |

Pour chaque récurrence détectée, les prochaines occurrences sont projetées sur la fenêtre demandée et leur montant moyen est ajouté mois par mois.

> **Tolérance** : un client facturé mensuellement mais avec quelques jours de décalage d'un mois à l'autre reste correctement classifié en « mensuel » — la plage 20-50 jours absorbe les variations normales de durée de mois et les légers retards.

#### 4. Projection finale
La valeur projetée retenue pour chaque mois est le **maximum** entre la projection linéaire et la projection récurrente, afin de ne jamais sous-estimer un revenu contractuel certain.

#### 5. Score de santé financière (0-100)
Calculé à partir de trois indicateurs pondérés :

| Indicateur | Poids | Calcul |
|---|---|---|
| Tendance du CA (6 mois) | 40 % | hausse=100, stable=60, baisse=20 |
| Taux de factures payées | 40 % | `payées / total × 100` |
| Absence de retards | 20 % | `100 − (nb en retard × 10)` |

#### 6. Indicateur de tendance
Comparaison du premier et du dernier mois sur les 3 derniers mois réels : +5% → `up`, −5% → `down`, sinon `stable`.

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
