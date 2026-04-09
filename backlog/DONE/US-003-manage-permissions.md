# US-003 - Gerer les permissions de base

## User Story

En tant que super-admin, je peux definir les permissions de base afin d'alimenter les controles d'acces.

## Valeur

Fournir le socle de permissions reutilisable par les policies et les futurs controles d'acces plateforme.

## Dependances

- depend de `US-002`

## Scope backend

- endpoint API de creation d'une permission de base
- validation des donnees entrantes
- service metier pour garantir l'unicite des codes de permissions
- rattachement d'une permission a un role global
- relation Eloquent `roles <-> permissions`
- point d'appui policy via `User::hasPermission()`

## Criteres d'acceptation

- une permission peut etre creee avec un code unique
- une permission peut etre rattachee a un role
- les policies peuvent s'appuyer sur ces permissions

## Verification

- route `POST /api/v1/super-admin/permissions` presente
- route `POST /api/v1/super-admin/permissions/{permission}/assign-role` presente
- `php artisan test tests/Feature/Api/ManagePermissionsTest.php` : 4 tests passes
- `php artisan test tests/Unit/PermissionPolicyTest.php` : 2 tests passes
- regression `php artisan test tests/Feature/Api/ManagePlatformRolesTest.php` : 4 tests passes
- regression `php artisan test tests/Feature/Api/CreateSuperAdminTest.php` : 2 tests passes
- regression `php artisan test tests/Feature/Api/CreateTenantTest.php` : 2 tests passes

## Resultat

- creation des permissions de base implementee
- rattachement des permissions aux roles globaux implemente
- verification policy sur `manage-platform-permissions` en place
- `TenantRequest` recree pour retablir la cohesion du socle backend existant
