# US-009 - Gerer les departements

## User Story

En tant qu'admin tenant, je peux creer des departements pour structurer les employes.

## Valeur

Permettre l'organisation interne du tenant avant la creation des employes et leurs affectations.

## Dependances

- depend de `US-008`

## Scope backend

- endpoint API de creation d'un departement
- endpoint API de listing des departements d'un tenant
- endpoint API de modification d'un departement
- unicite du code departement dans le tenant
- rattachement optionnel a une agence du meme tenant

## Criteres d'acceptation

- un departement peut etre cree, liste et modifie
- le code du departement est unique dans le tenant
- le rattachement optionnel a une agence est possible

## Verification

- route `POST /api/v1/departments` presente
- route `GET /api/v1/tenants/{tenant}/departments` presente
- route `PUT /api/v1/departments/{department}` presente
- `php artisan test tests/Feature/Api/ManageDepartmentsTest.php` : 5 tests passes
- regression `php artisan test tests/Feature/Api/CreateTenantAdminTest.php tests/Feature/Api/CreateMainBranchTest.php tests/Feature/Api/CreateTenantTest.php tests/Feature/Api/ManageOffersTest.php tests/Feature/Api/AttachModulesToOfferTest.php tests/Feature/Api/ManagePermissionsTest.php tests/Feature/Api/ManagePlatformRolesTest.php tests/Feature/Api/CreateSuperAdminTest.php` : 24 tests passes

## Resultat

- gestion backend des departements implementee
- unicite metier du code departement protegee par tenant
- rattachement optionnel a une agence du meme tenant verifie
- incoherences anciennes `BranchRepository`, `RoleRepository`, `OfferRepository` et `PermissionRepository` corrigees pour rester conformes a leurs interfaces
