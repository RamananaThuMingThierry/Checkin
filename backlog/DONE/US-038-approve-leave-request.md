# US-038 - Approuver une demande de conge

## User Story

En tant que RH ou manager, je peux approuver une demande de conge afin de valider une absence planifiee.

## Valeur

Faire passer le module conges d'une simple saisie a un vrai workflow RH exploitable.

## Dependances

- depend de `US-033`
- depend de `US-037`
- prepare `US-040` et `US-041`

## Scope backend

- endpoint API de transition d'une `leave_request` vers `approved`
- validation des transitions autorisees depuis le statut courant
- tracabilite minimale de la date et de l'auteur d'approbation si disponible
- prevention des references invalides et doubles approbations
- test feature d'approbation de demande de conge

## Criteres d'acceptation

- une demande en attente peut etre approuvee
- une demande deja traitee ne peut pas etre approuvee a nouveau de maniere incoherente
- le statut final est exploitable pour le planning et le reporting

## Verification

- test feature d'approbation ajoute
- verification des transitions de statut et du resultat persiste
