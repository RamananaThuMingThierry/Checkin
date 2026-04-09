# US-033 - Soumettre une demande de conge

## User Story

En tant qu'employe ou RH, je peux soumettre une demande de conge afin de tracer une absence planifiee.

## Valeur

Rendre les absences previsionnelles visibles et exploitables dans le cycle RH.

## Dependances

- depend de `US-010`
- depend de `US-032`
- prepare les futurs workflows d'approbation

## Scope backend

- endpoint API de creation d'une `leave_request`
- rattachement a un employe et a un type de conge du tenant
- validation de la periode demandee et du statut initial
- prevention des references invalides et des periodes incoherentes
- test feature de soumission de demande de conge

## Criteres d'acceptation

- une demande de conge peut etre soumise pour un employe
- la periode de conge est validee correctement
- le statut initial est exploitable pour la suite du workflow

## Verification

- test feature de creation de demande ajoute
- verification des references tenant, de la periode et du statut initial
