# US-036 - Exporter le reporting de presence

## User Story

En tant que RH ou manager, je peux exporter le reporting de presence afin de partager ou retraiter les donnees hors de l'application.

## Valeur

Transformer le reporting backend en livrable directement exploitable pour les operations RH.

## Dependances

- depend de `US-030`
- depend de `US-031`

## Scope backend

- endpoint API d'export des presences ou du reporting retards/absences
- format d'export simple et stable comme CSV
- reprise des filtres periode, tenant, agence et departement
- nommage de fichier coherent
- test feature d'export et de structure de sortie

## Criteres d'acceptation

- un reporting de presence peut etre exporte dans un format simple
- les filtres metier existants sont respectes
- la sortie est exploitable sans retraitement complexe

## Verification

- test feature d'export CSV ajoute
- verification du contenu, du filtrage et des en-tetes de reponse
