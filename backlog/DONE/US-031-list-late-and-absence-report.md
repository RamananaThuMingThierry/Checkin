# US-031 - Lister les retards et absences sur une periode

## User Story

En tant que RH ou manager, je peux lister les retards et absences sur une periode afin de piloter les ecarts de presence.

## Valeur

Rendre le reporting operationnel actionnable sans attendre le frontend avance.

## Dependances

- depend de `US-021`
- depend de `US-030`

## Scope backend

- endpoint API de reporting sur une plage de dates
- identification des retards et des absences exploitables
- aggregation simple par employe et par jour
- filtres par tenant et options de filtrage metier minimales
- test feature de reporting sur periode

## Criteres d'acceptation

- les retards peuvent etre listes sur une periode donnee
- les absences exploitables sont visibles dans la meme vue de reporting
- la reponse permet un usage RH et manager sans retraitement complexe

## Verification

- test feature de reporting retards et absences ajoute
- verification des cas avec presence normale, retard et absence
