# Context Map

## Domaines

### Core Domain

- pointage
- presence journaliere
- consolidation RH operationnelle

### Supporting Domains

- organisation entreprise
- IAM
- conges et jours feries

### Generic Domains

- billing
- activite et audit
- settings

## Relations

- `tenant` possede `branches`, `employees`, `users`, `settings`
- `employee` appartient a un tenant et peut etre rattache a une agence, un departement et un horaire
- `attendance_logs` nourrit `attendance_records`
- `leave_requests` et `holidays` influencent `attendance_records`
- `offers`, `subscriptions` et `tenant_modules` pilotent l'acces fonctionnel
