# 04 - Epics

## Epic 1 - Fondation produit

- structure documentaire
- socle API backend
- conventions d'architecture
- utilisateur super-admin plateforme
- authentification et autorisation

## Epic 2 - IAM et provisioning plateforme

- roles plateforme
- permissions
- seed initial de securite
- attribution des roles

## Epic 3 - Catalogue commercial

- modules
- offres
- composition des offres

## Epic 4 - Organisation entreprise

- tenants
- agences
- admin tenant
- departements
- parametres

## Epic 5 - RH

- employes
- horaires
- affectations
- conges

## Epic 6 - Pointage

- appareils
- scans
- consolidation journaliere
- pauses et anomalies

## Epic 7 - Reporting operationnel

- suivi des presences
- retards
- absences
- export

## Epic 8 - Billing SaaS

- abonnements
- factures
- paiements

## Sequencement recommande

1. super-admin plateforme
2. roles et permissions de base
3. catalogue offres et modules
4. creation entreprise
5. agence principale et admin tenant
6. structures RH
7. pointage brut
8. consolidation de presence
9. billing

## Premiere vague de user stories backend

- US-001 : creer le super-admin plateforme
- US-002 : gerer les roles de base plateforme
- US-003 : gerer les permissions de base
- US-004 : gerer les offres
- US-005 : rattacher les modules a une offre
- US-006 : creer une entreprise cliente
- US-007 : creer l'agence principale de l'entreprise
- US-008 : creer l'administrateur du tenant
- US-009 : gerer les departements
- US-010 : creer un employe
- US-011 : aligner la structure projet backend
- US-012 : creer un horaire de travail
- US-013 : affecter un horaire a un employe
- US-014 : enregistrer un appareil de pointage
- US-015 : associer un appareil a une agence
- US-016 : enregistrer un scan de pointage brut
- US-017 : identifier l'employe depuis un badge ou un identifiant
- US-018 : rejeter un scan invalide
- US-019 : lister les scans bruts d'une journee
- US-020 : consolider une journee de presence
- US-021 : signaler une anomalie de pointage

## Seconde vague de user stories backend

- US-022 : authentifier un utilisateur plateforme ou tenant
- US-023 : exposer le profil de l'utilisateur authentifie
- US-024 : creer et lister le catalogue de modules
- US-025 : souscrire une offre pour un tenant
- US-026 : activer les modules du tenant depuis l'abonnement

## Troisieme vague de user stories backend

- US-027 : generer une facture d'abonnement
- US-028 : lister les factures d'un tenant
- US-029 : enregistrer un paiement d'abonnement
- US-030 : lister les presences consolidees d'une journee
- US-031 : lister les retards et absences sur une periode
