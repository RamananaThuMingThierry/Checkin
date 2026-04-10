# US-FE-SA-006 - Gerer les parametres plateforme depuis le dashboard super-admin

## User story

En tant que super-admin, je peux consulter et modifier les parametres globaux visibles du dashboard afin de garder un cadre plateforme coherent.

## Valeur

Donner un point de pilotage central aux reglages transverses du shell super-admin sans melanger les parametres metier des tenants.

## Dependances

- depend de `US-FE-SA-001`
- depend de la definition des parametres plateforme exposes au frontend

## Perimetre

- ecran React dedie aux parametres plateforme
- affichage des reglages globaux visibles dans le shell
- edition des options confirmees comme modifiables
- restitution des erreurs et du resultat de mise a jour

## Definition of Ready

- liste des parametres plateforme a exposer validee
- endpoint de lecture et de mise a jour confirme ou backloge
- regles de validation connues
- emplacement de l'ecran confirme dans `/super-admin/settings`

## Criteres d'acceptation

- le super-admin peut consulter les parametres plateforme exposes
- les champs modifiables peuvent etre mis a jour depuis l'interface
- les erreurs backend sont visibles
- l'ecran ne montre pas les parametres internes d'un tenant

## Verification

- build frontend reussi
- verification manuelle de consultation des parametres
- verification manuelle d'une mise a jour valide
- verification manuelle d'une erreur de validation backend

## Definition of Done

- implementation frontend faite
- verification effectuee
- documentation backlog maintenue
- criteres QA explicites et verifies
