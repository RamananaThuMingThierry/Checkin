# 01 - PRD

## Objectif produit

Definir les fonctionnalites, les utilisateurs, les flux principaux et les criteres d'acceptation du produit.

## Orientation de livraison

- phase 1 : backend Laravel
- phase 2 : frontend React
- integration via API versionnee

## Personas

- super-admin plateforme
- admin tenant
- RH
- manager
- employe

## Fonctionnalites coeur

- gestion du super-admin plateforme
- gestion des roles et permissions
- gestion du catalogue d'offres et modules
- creation des entreprises clientes et de leurs agences
- gestion des employes
- gestion des horaires et affectations
- pointage et consolidation des presences
- gestion des conges et jours feries
- facturation, abonnements et modules

## Contraintes de conception

- backend structure avec interfaces, repositories, services, requests et policies
- frontend React branche sur une API stable
- les user stories sont livrees une par une
- les stories suivent le flux metier reel de provisioning

## Flux principaux

### Flux 1 - Provisionner une entreprise

- creer le super-admin plateforme
- preparer les roles et permissions de base
- definir le catalogue d'offres
- creer l'entreprise cliente
- creer son agence principale
- creer l'admin du tenant

### Flux 2 - Enregistrer le pointage

- enregistrer un appareil de pointage rattache a un tenant
- associer l'appareil a une agence autorisee si necessaire
- recevoir un scan brut depuis une source connue
- identifier l'employe a partir du badge ou de l'identifiant recu
- rejeter les scans invalides ou non autorises

### Flux 3 - Consolider la journee de presence

- consolider les scans valides de la journee
- produire un enregistrement de presence journalier
- signaler les anomalies exploitables par les equipes RH

### Flux 4 - Gerer les abonnements

- authentifier l'utilisateur qui administre la plateforme ou le tenant
- exposer son contexte d'acces et son profil courant
- maintenir le catalogue de modules vendables
- souscrire une offre pour un tenant
- activer les modules du tenant selon l'abonnement

### Flux 5 - Facturer un abonnement

- generer une facture a partir d'un abonnement actif
- exposer l'historique des factures d'un tenant
- enregistrer un paiement et mettre a jour le statut de la facture

### Flux 6 - Suivre les presences consolidees

- lister les enregistrements de presence produits par la consolidation
- exposer les retards et absences sur une periode exploitable par RH et managers
- preparer l'export et les tableaux de bord futurs

## Criteres d'acceptation

Chaque fonctionnalite devra inclure :

- contexte
- acteur
- preconditions
- scenario nominal
- erreurs attendues
- resultat attendu
