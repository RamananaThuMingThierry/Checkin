# US-015 - Associer un appareil a une agence

## User Story

En tant qu'admin tenant, je peux associer un appareil a une agence afin de localiser les scans de pointage.

## Valeur

Permettre de savoir depuis quelle agence un scan a ete emis et preparer les controles de coherence du pointage.

## Dependances

- depend de `US-014`
- depend de `US-007`
- prepare `US-016`

## Scope backend

- endpoint API de rattachement d'un appareil a une agence
- validation minimale de `branch_id`
- verification de coherence entre tenant de l'appareil et tenant de l'agence
- persistance du rattachement sur `devices.branch_id`
- test feature nominal et de rejet

## Criteres d'acceptation

- un appareil peut etre rattache a une agence du meme tenant
- les incoherences tenant / agence / appareil sont rejetees
- l'association est persistante

## Verification

- test feature `AssignDeviceToBranchTest` ajoute pour le rattachement nominal
- verification des rejets sur appareil inconnu, agence d'un autre tenant et payload incomplet
