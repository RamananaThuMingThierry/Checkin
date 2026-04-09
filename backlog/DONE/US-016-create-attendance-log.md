# US-016 - Enregistrer un scan de pointage brut

## User Story

En tant qu'appareil de pointage, je peux transmettre un scan brut afin de conserver chaque evenement de pointage.

## Valeur

Constituer la source brute necessaire avant toute consolidation de presence.

## Dependances

- depend de `US-014`
- depend de `US-015`
- prepare `US-017`, `US-018` et `US-020`

## Scope backend

- endpoint API de creation d'un scan brut
- validation des donnees minimales de tracabilite du scan
- resolution du `tenant_id` et du `branch_id` depuis l'appareil autorise
- rejet explicite d'une source inconnue, inactive ou non rattachee a une agence
- test feature nominal et de rejet

## Criteres d'acceptation

- un scan brut peut etre enregistre
- le scan conserve les informations minimales de tracabilite
- une source invalide est rejetee clairement

## Verification

- test feature `CreateAttendanceLogTest` ajoute pour la creation nominale
- verification des rejets sur appareil inconnu, inactif, non rattache et payload invalide
