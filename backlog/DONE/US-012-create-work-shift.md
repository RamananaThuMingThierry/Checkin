# US-012 - Creer un horaire de travail

## User Story

En tant qu'admin tenant, je peux creer un horaire de travail afin de definir les plages de travail standard utilisees pour les affectations et le pointage.

## Valeur

Poser le referentiel des horaires avant les affectations employees et avant le calcul des presences, afin de garder des regles de temps coherentes dans tout le tenant.

## Dependances

- depend de `US-008`
- prepare les futures stories d'affectation d'horaire et de pointage

## Scope backend

- modele, repository et service `WorkShift`
- endpoint API de creation d'un horaire
- validation des donnees minimales d'un horaire
- verification de coherence du rattachement au tenant
- unicite du code d'horaire dans le tenant
- test feature de creation minimal

## Criteres d'acceptation

- un horaire peut etre cree avec un code unique dans le tenant
- un horaire contient les informations minimales de planification
- les donnees invalides sont rejetees clairement

## Definition of Ready

- donnees minimales d'un horaire confirmees
- regles de base sur les heures d'entree et de sortie clarifiees
- impact sur les futures affectations compris

## Definition of Done

- endpoint de creation present
- validation et service metier implementes
- test feature passe
- conventions backend respectees
