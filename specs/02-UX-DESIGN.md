# 02 - UX Design

## Objectif

Documenter les parcours, ecrans et principes d'experience utilisateur.

## Stack cible

- frontend React
- backend Laravel API-first
- les parcours backend prioritaires sont stabilises avant les ecrans React

## Principes UX

- parcours simples
- actions critiques explicites
- priorite aux tableaux de bord operationnels
- faible friction sur le pointage et le suivi

## Parcours a documenter

- onboarding tenant
- creation agence
- creation employe
- affectation horaire
- pointage entree et sortie
- validation conge
- consultation des presences
- gestion abonnement et factures

## Ecrans attendus

- tableau de bord
- liste employes
- fiche employe
- planning / horaires
- journal de pointage
- presences journalieres
- conges
- abonnements / facturation
- shell dashboard avec sidebar, header, footer, content et navigation filtree par role

## Notes UI

- structure frontend React attendue dans `resources/js` :
  - `api/`
  - `components/`
  - `data/`
  - `hooks/`
  - `layouts/`
  - `pages/`
  - `routes/`
  - `utils/`
- organisation des pages par parcours et user stories :
  - `pages/auth/`
  - `pages/dashboard/`
  - `pages/rh/leaves/`
  - `pages/rh/reporting/`

