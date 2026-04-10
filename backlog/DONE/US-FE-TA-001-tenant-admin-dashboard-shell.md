# US-FE-TA-001 - Isoler le dashboard tenant admin

## User story

En tant qu'admin tenant ou RH, je peux ouvrir un dashboard dedie au tenant afin d'acceder aux ecrans RH sans melanger les operations plateforme.

## Valeur

Clarifier le parcours du tenant et preparer un shell stable pour les ecrans de conges, planning et reporting.

## Dependances

- depend de `US-022`
- depend de `US-023`
- depend de `US-FE-005`

## Perimetre

- shell React dedie au tenant admin
- navigation propre aux operations RH du tenant
- exclusion des pages super-admin du parcours tenant
- redirection post-login non super-admin vers le shell tenant

## Definition of Ready

- profil authentifie du tenant disponible
- routes tenant identifiees
- navigation RH ciblee validee
- attentes responsive du sidebar et du header connues

## Criteres d'acceptation

- un utilisateur non super-admin arrive sur un dashboard tenant dedie
- la navigation du tenant ne montre pas les routes plateforme
- le shell expose au moins `overview`, `leaves`, `planning` et `reporting`
- le shell reste responsive sur desktop et mobile

## Verification

- build frontend reussi
- verification manuelle de la redirection vers le shell tenant
- verification manuelle du filtrage de navigation cote tenant
- verification manuelle du responsive sidebar/header

## Definition of Done

- implementation frontend faite
- verification effectuee
- documentation backlog maintenue
- criteres QA explicites et verifies
