# US-039 - Rejeter une demande de conge

## User Story

En tant que RH ou manager, je peux rejeter une demande de conge afin de fermer explicitement une demande non retenue.

## Valeur

Completer le workflow de decision RH avec une issue claire et tracable.

## Dependances

- depend de `US-033`
- depend de `US-037`
- complete `US-038`

## Scope backend

- endpoint API de transition d'une `leave_request` vers `rejected`
- saisie d'un motif minimal de rejet
- validation des transitions autorisees depuis le statut courant
- prevention des references invalides et rejets incoherents
- test feature de rejet de demande de conge

## Criteres d'acceptation

- une demande en attente peut etre rejetee
- un motif de rejet est conserve pour consultation future
- une demande deja traitee n'accepte pas un rejet incoherent

## Verification

- test feature de rejet ajoute
- verification du statut final et du motif persiste
