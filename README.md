# EL HELLA

Application de suivi des distributeurs et des boutiques (Laravel + Blade + Bootstrap 5 + jQuery).

## ⚠️ Sécurité — Compte administrateur par défaut

Le seeder crée un compte administrateur par défaut :

- **Email** : `admin@elhella.com`
- **Mot de passe** : `password`

**Ce mot de passe DOIT être changé dès la première connexion** (page Profil → Mettre à jour le mot de passe). Ne jamais utiliser ces identifiants tels quels en production.

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configurer `.env` (MySQL, locale) :

```env
DB_CONNECTION=mysql
DB_DATABASE=el_hella
DB_USERNAME=root
DB_PASSWORD=

APP_LOCALE=ar
APP_FALLBACK_LOCALE=fr
```

Créer la base puis migrer et semer :

```bash
php artisan migrate
php artisan db:seed
```

Build des assets front-end :

```bash
npm run build
# ou en développement :
npm run dev
```

Lancer les tests :

```bash
php artisan test
```

## Partie 1 — Initialisation, authentification, multilingue et rôles

Statut : ✅ terminée.

Contenu livré :
- Authentification (connexion, déconnexion, mot de passe oublié/réinitialisation, confirmation de mot de passe, profil).
- Rôles `admin` / `distributor` et permissions via Spatie Laravel Permission.
- Interface multilingue arabe (par défaut, RTL) / français (LTR), avec bouton de changement de langue mémorisé par session et par compte utilisateur.
- Middleware de langue et middleware de contrôle du statut actif (`is_active`).
- Layout Bootstrap 5 responsive avec barre latérale, barre supérieure et tableaux de bord admin/distributeur (vides mais fonctionnels), redirection automatique selon le rôle après connexion.
- Protection des routes par rôle (`role:admin`, `role:distributor`).
- Tests Feature : connexion admin/distributeur, échec de connexion, blocage des comptes inactifs, déconnexion, protection des routes croisées, changement de langue, seeding des rôles/permissions.

Non couvert par cette partie (parties suivantes) : boutiques, produits, visites, distributions, objectifs, alertes GPS, carte, rapports.
