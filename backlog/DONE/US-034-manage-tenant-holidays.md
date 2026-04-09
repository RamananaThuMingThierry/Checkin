# US-034 - Gerer les jours feries du tenant

## User Story

En tant que RH ou admin tenant, je peux creer et lister les jours feries du tenant afin d'ajuster les presences et les absences.

## Valeur

Fiabiliser les futurs calculs RH et le reporting en tenant compte du calendrier reel du tenant.

## Dependances

- depend de `US-006`
- prepare l'evolution du reporting et des conges

## Scope backend

- endpoint API de creation et de listing des `holidays`
- validation d'une date unique par tenant et du libelle associe
- filtrage strict par tenant et par annee si necessaire
- test feature de gestion des jours feries

## Criteres d'acceptation

- un tenant peut enregistrer un jour ferie
- les jours feries d'un tenant peuvent etre listes
- un doublon de date est rejete proprement

## Verification

- test feature de creation et de listing ajoute
- verification du filtrage tenant et du rejet des doublons de date
