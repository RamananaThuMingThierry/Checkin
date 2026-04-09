# US-001 - Creer le super-admin plateforme

## User Story

En tant que proprietaire de la plateforme, je peux creer le premier super-admin afin d'administrer le produit au niveau global.

## Valeur

Disposer d'un compte racine pour gerer la plateforme avant toute creation d'entreprise cliente.

## Scope backend

- creation du super-admin global
- validation des donnees utilisateur
- hash du mot de passe
- statut actif par defaut
- rattachement aux roles globaux a venir

## Criteres d'acceptation

- un utilisateur global peut etre cree sans `tenant_id`
- l'email est unique
- le mot de passe est correctement stocke
- le statut initial est `active`
- le compte peut etre identifie comme super-admin

## Verification

- migration ajoutee pour `users.is_super_admin`
- endpoint `POST /api/v1/super-admin/users` en place
- `php artisan test tests/Feature/Api/CreateSuperAdminTest.php` : 2 tests passes

## Resultat

- creation du premier super-admin implementee
- creation d'un second super-admin bloquee
- incoherence detectee sur `activity_logs.tenant_id` contournee pour rester conforme au schema actuel
