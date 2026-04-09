# AGENTS

Ce projet utilise une organisation par roles pour garder une execution claire et incremental.

## Orientation technique

- backend en premier
- Laravel pour le coeur metier et l'API
- React pour le frontend
- approche API-first pour decoupler backend et frontend

## Structure

- `agents/architect.md` : cadre technique, choix structurants, dette technique.
- `agents/dev.md` : implementation, conventions de code, livraison technique.
- `agents/pm.md` : pilotage, priorisation, coordination, suivi.
- `agents/po.md` : vision produit, besoins metier, arbitrage fonctionnel.
- `agents/qa.md` : strategie de test, verification, criteres de validation.
- `specs/` : documents de reference du produit et de l'architecture.
- `backlog/` : suivi des taches par etat.

## Regles de travail

1. Toujours partir du besoin metier avant d'ouvrir une tache technique.
2. Toute nouvelle fonctionnalite doit avoir une trace dans `specs/01-PRD.md` ou `specs/04-EPICS.md`.
3. Toute decision structurante doit etre documentee dans `specs/03-ARCHITECTURE.md`.
4. Une tache ne passe en `DONE` que si les criteres QA sont explicites.
5. Le backlog doit rester petit, actionnable et ordonne par priorite.
6. Les user stories sont traitees une par une.
7. Chaque user story existe comme fichier unique dans `backlog/TODO`, `backlog/WIP` ou `backlog/DONE`.
8. Une story passe en `DONE` seulement apres implementation et verification.

## Flux recommande

1. `po` formalise le besoin et la valeur attendue.
2. `pm` decoupe et priorise.
3. `architect` definit l'approche technique.
4. `dev` implemente par increment court.
5. `qa` verifie le comportement, les regressions et l'acceptation.

## Definition of Ready

- probleme compris
- valeur metier explicite
- perimetre defini
- dependances identifiees
- criteres d'acceptation rediges

## Definition of Done

- implementation terminee
- verification effectuee
- documentation mise a jour
- backlog deplace vers `backlog/DONE`
- risques restants explicites si necessaire
