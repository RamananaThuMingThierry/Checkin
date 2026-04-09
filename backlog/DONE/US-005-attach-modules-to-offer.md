# US-005 - Rattacher les modules a une offre

## User Story

En tant que super-admin, je peux composer une offre avec des modules afin de definir le contenu commercial vendu.

## Valeur

Permettre la composition commerciale du catalogue avant l'activation des offres pour les tenants.

## Dependances

- depend de `US-004`

## Scope backend

- endpoint API de rattachement d'un module a une offre
- relation Eloquent `offers <-> modules` via `offer_modules`
- prevention du doublon sur une meme offre
- tracabilite du flag `is_included`
- reponse API standardisee

## Criteres d'acceptation

- un module peut etre lie a une offre
- une offre ne contient pas deux fois le meme module
- la relation d'inclusion est traquee

## Verification

- route `POST /api/v1/super-admin/offers/{offer}/modules` presente
- `php artisan test tests/Feature/Api/AttachModulesToOfferTest.php` : 3 tests passes
- regression `php artisan test tests/Feature/Api/ManageOffersTest.php tests/Feature/Api/ManagePermissionsTest.php tests/Feature/Api/ManagePlatformRolesTest.php tests/Feature/Api/CreateSuperAdminTest.php tests/Feature/Api/CreateTenantTest.php` : 15 tests passes

## Resultat

- rattachement des modules aux offres implemente
- prevention metier du doublon module/offre implementee
- suivi du flag `is_included` verifie en test feature
