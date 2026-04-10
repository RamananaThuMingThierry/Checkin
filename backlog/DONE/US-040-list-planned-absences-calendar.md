# US-040 - Lister le calendrier des absences planifiees

## User Story

En tant que RH ou manager, je peux consulter un calendrier simple des absences planifiees afin d'anticiper les indisponibilites d'equipe.

## Valeur

Transformer les demandes approuvees en vue operationnelle utile pour le pilotage des equipes.

## Dependances

- depend de `US-038`
- depend de `US-034`
- prepare le frontend RH et les arbitrages de planning

## Scope backend

- endpoint API de listing des absences planifiees sur une periode
- agrégation minimale des conges approuves et jours feries du tenant
- filtres par agence, departement et employe si necessaire
- structure de sortie exploitable pour une vue calendrier ou agenda
- test feature de listing des absences planifiees

## Criteres d'acceptation

- les conges approuves sont visibles sur une periode donnee
- les jours feries du tenant peuvent etre exposes dans la meme vue ou un format compatible
- les filtres metier sont respectes

## Verification

- test feature de calendrier ajoute
- verification de la periode, des filtres et des types d'evenements retournes
