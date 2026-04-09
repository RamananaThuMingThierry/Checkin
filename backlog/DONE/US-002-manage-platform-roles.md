# US-002 - Gerer les roles de base plateforme

## User Story

En tant que super-admin, je peux definir les roles globaux de base afin de structurer les droits de la plateforme.

## Valeur

Poser le socle IAM plateforme pour preparer l'attribution des permissions et l'administration globale.

## Dependances

- depend de `US-001`

## Scope backend

- endpoint API de creation d'un role global
- validation des donnees entrantes
- service metier pour garantir l'unicite des codes globaux
- attribution d'un role global a un utilisateur global
- relations Eloquent `users <-> roles`

## Criteres d'acceptation

- un role global peut etre cree avec `tenant_id = null`
- les codes de roles globaux sont uniques
- un utilisateur peut recevoir un role global

## Verification

- route `POST /api/v1/super-admin/roles` presente
- route `POST /api/v1/super-admin/roles/{role}/assign` presente
- `php artisan test tests/Feature/Api/ManagePlatformRolesTest.php` : 4 tests passes
- regression `php artisan test tests/Feature/Api/ManagePlatformRolesTest.php tests/Feature/Api/CreateSuperAdminTest.php tests/Feature/Api/CreateTenantTest.php` : 8 tests passes

## Resultat

- creation des roles globaux implementee
- unicite metier des codes globaux protegee en service pour le cas `tenant_id = null`
- attribution des roles globaux a un utilisateur global implementee
- incoherences existantes de `US-006` corrigees pour retablir les tests backend deja livres
