# US-FE-004 - Exposer le rapport d'absences et retards dans le frontend

## User Story

En tant que RH ou manager, je peux consulter le rapport des absences et retards depuis l'interface React afin d'exploiter le reporting sans passer par des endpoints directs.

## Valeur

Rendre visible le reporting fiable, y compris les conges approuves distingues des absences reelles.

## Dependances

- depend de `US-041`
- depend de `US-FE-001`

## Scope frontend

- ecran rapport absences et retards
- filtres de periode et perimetre
- tableau des items `late`, `absence` et `approved_leave`
- lien d'export CSV

## Criteres d'acceptation

- le rapport est consultable depuis le frontend
- les conges approuves sont distingues visuellement
- l'export CSV est declenchable depuis l'UI

## Verification

- build frontend reussi
- verification manuelle du rapport et de l'export
