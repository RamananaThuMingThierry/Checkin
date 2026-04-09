# US-035 - Gerer les parametres du tenant

## User Story

En tant qu'admin tenant, je peux gerer les parametres metier du tenant afin d'adapter le comportement de l'application a mon contexte.

## Valeur

Centraliser les reglages necessaires avant le frontend avance et avant les raffinements metier du pointage.

## Dependances

- depend de `US-006`
- prepare les evolutions de pointage, RH et reporting

## Scope backend

- endpoint API de creation ou mise a jour des `settings` du tenant
- lecture des parametres courants du tenant
- stockage structure des cles metier minimales
- validation d'un payload de configuration simple
- test feature de lecture et mise a jour des parametres tenant

## Criteres d'acceptation

- un tenant peut consulter ses parametres
- un tenant peut mettre a jour ses parametres metier
- les valeurs invalides sont rejetees clairement

## Verification

- test feature de lecture et d'update ajoute
- verification de la persistance des cles et du filtrage par tenant
