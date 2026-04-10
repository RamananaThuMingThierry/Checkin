# US-FE-TA-003 - Connecter le calendrier des absences planifiees cote tenant

## User story

En tant que RH ou manager, je peux consulter le calendrier des absences planifiees depuis le shell tenant afin d'anticiper les indisponibilites.

## Valeur

Transformer les conges approuves et jours feries en vue planning connectee et directement exploitable.

## Dependances

- depend de `US-040`
- depend de `US-FE-TA-001`

## Perimetre

- chargement du calendrier RH depuis l'API
- filtres de periode et perimetre
- affichage des evenements `approved_leave` et `holiday`
- restitution claire de l'etat vide ou erreur

## Definition of Ready

- endpoint calendrier confirme
- structure des evenements connue
- plage par defaut et filtres attendus identifies
- emplacement de l'ecran valide dans `/tenant-dashboard/planning`

## Criteres d'acceptation

- les absences planifiees sont visibles sur une plage donnee
- les jours feries sont visibles dans la meme vue
- les filtres RH sont appliques
- les etats vide et erreur sont visibles

## Verification

- build frontend reussi
- verification manuelle du calendrier charge
- verification manuelle des filtres
- verification manuelle d'un cas sans evenement

## Definition of Done

- implementation frontend faite
- verification effectuee
- documentation backlog maintenue
- criteres QA explicites et verifies
