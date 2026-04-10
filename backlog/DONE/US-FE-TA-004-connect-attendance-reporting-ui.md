# US-FE-TA-004 - Connecter le reporting des absences et retards cote tenant

## User story

En tant que RH ou manager, je peux consulter le rapport des absences et retards depuis le shell tenant afin d'exploiter le reporting sans passer par les endpoints directs.

## Valeur

Rendre visible le reporting fiable du tenant avec distinction claire des conges approuves et support de l'export.

## Dependances

- depend de `US-031`
- depend de `US-036`
- depend de `US-041`
- depend de `US-FE-TA-001`

## Perimetre

- chargement du rapport absences/retards
- filtres de periode et perimetre
- tableau des items `late`, `absence` et `approved_leave`
- declenchement de l'export CSV

## Definition of Ready

- endpoint rapport et endpoint export confirmes
- colonnes du tableau ciblees connues
- regles visuelles de distinction des statuts validees
- emplacement de l'ecran valide dans `/tenant-dashboard/reporting`

## Criteres d'acceptation

- le rapport est consultable depuis le shell tenant
- les conges approuves sont distingues visuellement
- l'export CSV est declenchable depuis l'UI
- les erreurs et etats vides sont visibles

## Verification

- build frontend reussi
- verification manuelle du rapport charge
- verification manuelle de la distinction visuelle des statuts
- verification manuelle du declenchement de l'export

## Definition of Done

- implementation frontend faite
- verification effectuee
- documentation backlog maintenue
- criteres QA explicites et verifies
