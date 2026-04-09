# US-004 - Gerer les offres

## User Story

En tant que super-admin, je peux creer et gerer les offres commerciales de la plateforme.

## Valeur

Structurer le catalogue commercial de base avant la composition des offres avec les modules.

## Dependances

- depend de `US-001`

## Scope backend

- endpoint API de creation d'une offre
- validation des donnees entrantes
- service metier pour garantir l'unicite du code offre
- gestion des valeurs de tarification et des statuts d'activation
- reponse API standardisee

## Criteres d'acceptation

- une offre peut etre creee avec nom, code et tarification
- le code offre est unique
- une offre peut etre active ou inactive

## Verification

- route `POST /api/v1/super-admin/offers` presente
- `php artisan test tests/Feature/Api/ManageOffersTest.php` : 3 tests passes
- regression `php artisan test tests/Feature/Api/CreateSuperAdminTest.php tests/Feature/Api/ManagePlatformRolesTest.php tests/Feature/Api/ManagePermissionsTest.php tests/Feature/Api/CreateTenantTest.php` : 12 tests passes

## Resultat

- creation des offres commerciales implementee
- unicite metier du code offre protegee en service
- statuts actif/inactif et tarification couverts par les tests feature
