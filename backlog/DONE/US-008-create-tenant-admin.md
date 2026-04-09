# US-008 - Creer l'administrateur du tenant

## User Story

En tant que super-admin, je peux creer le premier administrateur du tenant pour permettre la prise en main de l'entreprise cliente.

## Valeur

Permettre au client d'acceder a son espace des que le tenant et son agence principale sont provisionnes.

## Dependances

- depend de `US-006`
- depend idealement de `US-007`

## Scope backend

- endpoint API de creation du premier administrateur tenant
- validation des donnees entrantes
- creation d'un utilisateur scope au tenant
- rattachement automatique a l'agence principale si elle existe
- creation ou reutilisation d'un role tenant `tenant-admin` puis attribution a l'utilisateur

## Criteres d'acceptation

- un administrateur tenant peut etre cree avec rattachement a l'entreprise
- l'email est unique
- un role d'administration tenant peut lui etre attribue

## Verification

- route `POST /api/v1/tenant-admin/users` presente
- `php artisan test tests/Feature/Api/CreateTenantAdminTest.php` : 3 tests passes
- regression `php artisan test tests/Feature/Api/CreateTenantTest.php tests/Feature/Api/CreateMainBranchTest.php tests/Feature/Api/ManageOffersTest.php tests/Feature/Api/AttachModulesToOfferTest.php tests/Feature/Api/ManagePermissionsTest.php tests/Feature/Api/ManagePlatformRolesTest.php tests/Feature/Api/CreateSuperAdminTest.php` : 21 tests passes

## Resultat

- creation du premier administrateur tenant implementee
- rattachement automatique a l'agence principale si disponible
- role tenant `tenant-admin` cree ou reutilise puis attribue automatiquement
