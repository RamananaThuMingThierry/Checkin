# US-010A - Poser le socle backend de creation d'employe

## User Story

En tant qu'admin tenant, je peux disposer d'une API minimale de creation d'employe afin de demarrer l'enregistrement RH sans attendre tout le perimetre employe.

## Valeur

Decouper `US-010` en un increment plus petit et executable pour livrer le coeur de la creation d'employe avant les enrichissements futurs.

## Dependances

- depend de `US-007`
- depend de `US-009`
- prepare `US-010`

## Scope backend

- modele, repository et service `Employee`
- endpoint API de creation d'un employe
- validation des donnees minimales
- verification de coherence `tenant_id`, `branch_id`, `department_id`
- unicite de `employee_code` dans le tenant
- test feature de creation minimal

## Criteres d'acceptation

- un employe peut etre cree avec un code unique dans le tenant
- l'employe peut etre rattache a une agence et un departement
- les donnees minimales sont validees

## Definition of Ready

- tenant disponible
- agence principale disponible si rattachement agence
- departement disponible si rattachement departement
- donnees minimales employees confirmees

## Definition of Done

- endpoint de creation present
- validation et service metier implementes
- test feature passe
- aucune regression sur le provisioning tenant
