# US-011 - Aligner la structure projet backend

## User Story

En tant qu'equipe produit et technique, nous pouvons stabiliser une structure projet backend coherente afin de livrer les prochaines user stories plus vite, avec moins d'ambiguite et moins de dette technique.

## Valeur

Reduire le cout de maintenance, fiabiliser les conventions de developpement et preparer une base claire pour la suite des stories backend Laravel puis pour le frontend React branche sur une API stable.

## Contexte

- la structure cible documentee doit correspondre a la structure reelle du code
- certaines conventions doivent etre unifiees avant d'ajouter plus de domaines metier
- cette story est un enabler technique pour limiter la dette sur les prochaines livraisons

## Scope

- clarifier et appliquer la convention de dossiers backend
- aligner les namespaces et chemins reels des composants
- unifier la convention des contrats applicatifs
- verifier que les points d'entree API restent lisibles
- maintenir les tests critiques apres reorganisation

## Hors scope

- refonte fonctionnelle des domaines existants
- introduction du frontend React
- modularisation avancee ou decoupage en packages

## Dependances

- depend de `US-010A`
- prepare la suite de `US-010` et des stories backend suivantes

## Criteres d'acceptation

- la structure cible est explicite dans `specs/03-ARCHITECTURE.md`
- le code backend suit une convention unique pour les interfaces, services et repositories
- les namespaces sont alignes avec les chemins de fichiers
- les routes et controleurs API restent localisables simplement
- les tests de regression critiques passent apres reorganisation

## Definition of Ready

- ambiguites de structure identifiees
- convention cible choisie
- impact sur les stories en cours compris
- perimetre de reorganisation borne

## Definition of Done

- structure projet backend alignee
- documentation architecture mise a jour
- verification technique effectuee
- impacts residuels explicites si certains ecarts sont reportes
