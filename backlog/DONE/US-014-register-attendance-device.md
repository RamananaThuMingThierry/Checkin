# US-014 - Enregistrer un appareil de pointage

## User Story

En tant qu'admin tenant, je peux enregistrer un appareil de pointage afin d'autoriser une source de scan fiable pour le tenant.

## Valeur

Poser le referentiel des appareils avant la collecte des scans et garder la maitrise des sources autorisees.

## Dependances

- depend de `US-007`
- prepare `US-015` et `US-016`

## Scope backend

- service de creation d'un appareil de pointage
- endpoint API de creation d'un appareil
- validation des donnees minimales et des valeurs enumerees
- verification de coherence `tenant_id` et `branch_id` si l'agence est fournie
- test feature de creation et de rejet

## Criteres d'acceptation

- un appareil peut etre cree pour un tenant
- les donnees minimales de l'appareil sont validees
- un appareil invalide est rejete clairement

## Verification

- test feature `RegisterAttendanceDeviceTest` ajoute pour la creation nominale
- verification des rejets sur code duplique, agence d'un autre tenant et payload invalide
