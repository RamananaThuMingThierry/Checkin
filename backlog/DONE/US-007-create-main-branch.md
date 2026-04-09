# US-007 - Creer l'agence principale de l'entreprise

## User Story

En tant que super-admin, je peux creer l'agence principale d'une entreprise cliente pour rattacher ses donnees operationnelles.

## Valeur

Permettre l'ouverture operationnelle du tenant avant la creation de son administrateur et des structures RH.

## Dependances

- depend de `US-006`

## Scope backend

- endpoint API de creation de l'agence principale
- validation des donnees entrantes
- service metier de creation pour une entreprise existante
- unicite du code agence dans le tenant
- identification explicite de l'agence principale via `is_main`

## Criteres d'acceptation

- une agence peut etre creee pour une entreprise existante
- le code d'agence est unique dans l'entreprise
- l'agence principale est identifiable

## Verification

- route `POST /api/v1/branches/main` presente
- migration ajoutee pour `branches.is_main`
- `php artisan test tests/Feature/Api/CreateMainBranchTest.php` : 3 tests passes
- regression `php artisan test tests/Feature/Api/CreateTenantTest.php tests/Feature/Api/ManageOffersTest.php tests/Feature/Api/AttachModulesToOfferTest.php tests/Feature/Api/ManagePermissionsTest.php tests/Feature/Api/ManagePlatformRolesTest.php tests/Feature/Api/CreateSuperAdminTest.php` : 18 tests passes

## Resultat

- creation de l'agence principale implementee
- unicite du code agence protegee au niveau metier dans le tenant
- identification explicite de l'agence principale ajoutee via `is_main`
