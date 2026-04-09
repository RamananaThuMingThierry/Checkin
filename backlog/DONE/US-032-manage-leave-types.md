# US-032 - Gerer les types de conges

## User Story

En tant que RH ou admin tenant, je peux creer et lister les types de conges afin de parametrer les absences autorisees du tenant.

## Valeur

Poser la base fonctionnelle necessaire avant toute gestion exploitable des demandes de conges.

## Dependances

- depend de `US-009`
- prepare `US-033`

## Scope backend

- endpoint API de creation et de listing des `leave_types`
- validation des champs metier minimaux d'un type de conge
- filtrage strict par tenant
- rejet des doublons exploitables
- test feature de gestion du catalogue de types de conges

## Criteres d'acceptation

- un tenant peut creer un type de conge
- les types de conges d'un tenant peuvent etre listes
- les doublons incoherents sont rejetes clairement

## Verification

- test feature de creation et de listing ajoute
- verification du filtrage par tenant et du rejet des doublons
