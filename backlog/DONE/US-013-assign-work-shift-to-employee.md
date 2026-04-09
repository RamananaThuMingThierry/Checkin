# US-013 - Affecter un horaire a un employe

## User Story

En tant qu'admin tenant, je peux affecter un horaire a un employe afin de preparer le calcul du pointage et des presences selon son planning de reference.

## Valeur

Relier le referentiel employees au referentiel horaires pour permettre les futures regles de pointage, de retards et de consolidation journaliere.

## Dependances

- depend de `US-010`
- depend de `US-012`
- prepare les futures stories de pointage et de presence

## Scope backend

- service d'affectation d'un horaire a un employe
- endpoint API de creation d'une affectation
- validation des donnees minimales d'affectation
- verification de coherence `tenant_id`, `employee_id`, `work_shift_id`
- test feature de creation minimal

## Criteres d'acceptation

- un employe peut recevoir un horaire de travail
- l'affectation respecte le tenant de l'employe et de l'horaire
- les donnees invalides sont rejetees clairement

## Definition of Ready

- regles minimales d'affectation confirmees
- date de debut d'affectation clarifiee
- relation employee / work shift comprise

## Definition of Done

- endpoint de creation present
- validation et service metier implementes
- test feature passe
- conventions backend respectees
