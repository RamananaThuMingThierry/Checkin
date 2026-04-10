# US-041 - Exclure les conges approuves du rapport d'absences

## User Story

En tant que RH ou manager, je veux que les conges approuves ne soient pas comptes comme absences injustifiees afin d'avoir un reporting fiable.

## Valeur

Fiabiliser le reporting RH en distinguant absence reelle et absence justifiee.

## Dependances

- depend de `US-031`
- depend de `US-038`
- peut exploiter `US-040`

## Scope backend

- adaptation du reporting des retards et absences pour tenir compte des `leave_requests` approuvees
- ajout d'un type explicite comme `approved_leave` ou exclusion configurable des absences justifiees
- conservation du comportement existant pour les retards
- test feature de non-remontee des absences couvertes par conge approuve
- verification des cas limites sur une plage multi-jours

## Criteres d'acceptation

- une journee couverte par un conge approuve n'apparait plus comme absence injustifiee
- le reporting reste correct sur les autres employes et les retards
- le resultat distingue clairement les absences justifiees si elles sont exposees

## Verification

- test feature de reporting ajuste
- verification des cas avec conge approuve, sans conge et sur plusieurs jours
