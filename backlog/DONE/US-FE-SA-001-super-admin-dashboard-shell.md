# US-FE-SA-001 - Isoler le dashboard super-admin

## User story

En tant que super-admin, je peux ouvrir un dashboard plateforme dedie afin de piloter le provisioning et le billing sans voir les ecrans metier du tenant.

## Valeur

Eviter le melange entre administration plateforme et operations RH du tenant pour garder un frontend clair et pilotable.

## Dependances

- depend de `US-022`
- depend de `US-023`

## Perimetre

- shell React dedie au super-admin
- navigation propre aux operations plateforme
- exclusion des pages tenant du parcours super-admin
- affichage des stories frontend super-admin dans le dashboard

## Definition of Ready

- role super-admin et profil authentifie exposes par l'API
- routes frontend super-admin identifiees
- sections visibles du dashboard listees
- attentes responsive connues pour sidebar et header

## Criteres d'acceptation

- un super-admin connecte arrive sur un dashboard plateforme
- la navigation ne montre que les pages `tenant`, `offer`, `subscription`, `invoice`, `payment`
- les pages RH du tenant ne sont pas affichees au super-admin
- le shell garde la session et la navigation existantes

## Verification

- build frontend reussi
- verification manuelle de la redirection post-login super-admin
- verification manuelle du filtrage de navigation desktop et mobile
- verification manuelle de l'absence des pages tenant dans le shell super-admin

## Definition of Done

- implementation frontend faite
- verification du build effectuee
- structure `pages/super-admin` en place
- criteres QA explicites et verifies
