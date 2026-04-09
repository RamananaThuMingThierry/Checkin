# US-006 - Creer une entreprise cliente

## User Story

En tant que super-admin, je peux creer une entreprise cliente afin d'ouvrir un nouvel espace tenant dans la plateforme.

## Valeur

Permettre le provisioning d'un nouveau client apres mise en place du socle plateforme et commercial.

## Scope backend

- endpoint API de creation
- validation des donnees entrantes
- service de creation
- repository de persistence
- reponse API standardisee

## Criteres d'acceptation

- une entreprise peut etre creee avec `name` et `code`
- `code` est unique
- le statut initial est coherent avec les regles metier
- les erreurs de validation sont retournees proprement
- l'entreprise creee est scope pour les stories suivantes

## Definition of Done

- controller API
- request de validation
- interface et repository
- service metier
- test feature minimal

## Verification

- `php artisan route:list --path=api` : route `POST api/v1/tenants` presente
- `php artisan test tests/Feature/Api/CreateTenantTest.php` : 2 tests passes
- verification syntaxique PHP effectuee sur les fichiers crees

## Resultat

- story implementee et verifiee
- environnement de test ajuste pour utiliser `pointages_test` sur MySQL
