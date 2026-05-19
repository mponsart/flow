# Association Finance AI

> Application web professionnelle de gestion financière pour associations, optimisée IA, sécurisée, moderne et prête pour la production.

---

## Sommaire

- [Présentation](#présentation)
- [Fonctionnalités](#fonctionnalités)
- [Stack technique](#stack-technique)
- [Installation](#installation)
- [Configuration](#configuration)
- [Utilisation](#utilisation)
- [Sécurité](#sécurité)
- [Architecture](#architecture)
- [Exemples de données](#exemples-de-données)
- [Crédits](#crédits)

---

## Présentation

**Association Finance AI** est une application web moderne permettant à une association de gérer ses services mensuels/annuels, d’analyser sa rentabilité, de suivre ses revenus récurrents, de générer des prévisions et d’obtenir des analyses intelligentes via IA (Ollama Cloud).

L’interface est minimaliste, professionnelle, responsive, en dark mode, inspirée de Stripe/Linear/Vercel.

---

## Fonctionnalités

- Authentification Google OAuth2 (domaine `@groupe-speed.cloud` uniquement)
- Dashboard financier intelligent (MRR, ARR, cashflow, marges, alertes IA)
- Gestion clients (fiche détaillée, rentabilité, historique abonnements)
- Gestion services (mensuels/annuels, rentabilité, abonnés)
- Abonnements récurrents (cycle, renouvellement, statut)
- Suivi revenus/dépenses (historique, projections, catégories)
- Calculs financiers automatisés (MRR, ARR, marge, bénéfice, LTV...)
- Analyses IA (Ollama Cloud) : conseils, alertes, résumés, détection anomalies
- Rapports PDF/Excel, exports, résumés IA
- API REST sécurisée (dashboard, finances, auth)
- Sécurité avancée (CSRF, validation, rate limiting, logs)
- UI moderne : sidebar, topbar, cartes KPI, graphiques Chart.js, dark mode

---

## Stack technique

- **Backend** : PHP 8.3+, Laravel 13, SQLite
- **Frontend** : Blade Laravel, TailwindCSS CDN, Chart.js CDN, JavaScript vanilla
- **IA** : Ollama Cloud (API)
- **Auth** : Google OAuth2 (Laravel Socialite, domaine restreint)

**Aucune dépendance front, aucun build, tout fonctionne immédiatement après installation.**

---

## Installation

1. **Cloner le dépôt**
	```bash
	git clone <repo-url>
	cd flow
	```
2. **Installer les dépendances PHP**
	```bash
	composer install
	```
3. **Configurer l’environnement**
	- Copier `.env.example` en `.env` et adapter les variables (voir [Configuration](#configuration))
4. **Générer la clé d’application**
	```bash
	php artisan key:generate
	```
5. **Lancer les migrations et seeders**
	```bash
	php artisan migrate --seed
	```
6. **Démarrer le serveur**
	```bash
	php artisan serve
	```
7. **Accéder à l’application**
	- Ouvrir [http://localhost:8000](http://localhost:8000)

---

## Configuration

### Variables d’environnement principales

- `APP_NAME=Association Finance AI`
- `APP_ENV=local|production`
- `APP_KEY=...`
- `DB_CONNECTION=sqlite`
- `DB_DATABASE=.../database/database.sqlite`
- `GOOGLE_CLIENT_ID=...` *(OAuth2)*
- `GOOGLE_CLIENT_SECRET=...`
- `GOOGLE_REDIRECT_URI=http://localhost:8000/auth/callback`
- `OLLAMA_CLOUD_API_KEY=...`

**Important :** Seuls les comptes Google du domaine `@groupe-speed.cloud` sont autorisés à se connecter.

---

## Utilisation

1. **Connexion** : via Google (domaine restreint)
2. **Navigation** : sidebar moderne, dashboard, gestion clients/services/abonnements
3. **Dashboard** : KPIs, graphiques, alertes IA, export rapports
4. **Gestion** : création/édition/suppression clients, services, abonnements, dépenses
5. **Analyses IA** : accès aux conseils et résumés financiers générés par Ollama Cloud

---

## Sécurité

- Authentification Google OAuth2 (domaine restreint, middleware dédié)
- Sessions sécurisées, CSRF, validation stricte backend
- Rate limiting, logs sécurité, protection SQLi
- API REST sécurisée (auth, rate limit)

---

## Architecture

- **Controllers** : logique HTTP, REST, dashboard
- **Services** : IA, finances, projections, rapports
- **Repositories** : accès données, requêtes complexes
- **Helpers** : calculs financiers
- **Middleware** : auth, restriction domaine Google
- **Policies** : autorisations
- **Form Requests** : validation
- **Blade Components** : UI réutilisable (KPI, tables, cards, modals)

---

## Exemples de données

Des seeders fournissent des exemples de clients, services, abonnements, revenus, dépenses pour tester l’application immédiatement.

---

## Crédits

Développé par Association Finance AI. Basé sur Laravel 13. UI inspirée de Stripe, Linear, Vercel.

---

**Licence MIT**
