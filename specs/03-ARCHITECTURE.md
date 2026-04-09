# 03 - Architecture

## Objectif

Poser l'architecture cible et les regles structurantes du projet.

## Contexte technique

- framework principal : Laravel
- frontend cible : React
- base relationnelle
- application multi-tenant
- approche API-first

## Domaines principaux

- IAM et administration
- organisation entreprise
- RH
- pointage
- facturation SaaS

## Principes

- separer les domaines metier
- garder la logique metier hors des couches de presentation
- tracer les decisions structurantes
- preferer des conventions simples et coherentes
- utiliser des interfaces pour les dependances metier
- isoler l'acces aux donnees dans des repositories
- concentrer la logique metier dans des services
- utiliser les Form Requests pour la validation
- utiliser les Policies pour l'autorisation
- exposer l'API via des controleurs fins et des ressources claires

## Structure applicative cible

- `app/Interfaces`
- `app/Repositories`
- `app/Services`
- `app/Policies`
- `app/Http/Controllers/Api`
- `app/Http/Requests`
- `app/Http/Resources`
- `routes/api.php`

## Structure projet a stabiliser

- garder une structure coherente avec l'implementation reelle Laravel
- centraliser les contrats metier dans `app/Interfaces`
- eviter la coexistence de plusieurs conventions concurrentes pour les contrats
- organiser les composants par responsabilite applicative avant une modularisation plus forte
- limiter les deplacements de fichiers aux increments qui reduisent la dette ou clarifient le code

## Criteres de bonne structure

- un dossier a une responsabilite claire
- les interfaces, services et repositories suivent une convention unique
- les namespaces correspondent aux chemins reels
- les points d'entree API restent simples a localiser
- les tests refletent la structure cible et couvrent les conventions critiques

## Regles de couche

- un controller recoit la requete et delegue
- une request valide les donnees entrantes
- une policy verifie les droits
- un service orchestre la logique metier
- un repository encapsule l'acces aux donnees
- une interface definit le contrat entre couches

## Strategie de livraison

- construire d'abord le backend
- livrer les user stories backend une par une
- stabiliser les contrats API avant le frontend React
- versionner l'API des le depart

## Decisions a documenter

- strategie multi-tenant
- organisation des modules Laravel
- politique d'autorisation
- calcul et consolidation du pointage
- gestion des integrations de paiement

## Risques techniques

- confusion entre logique Eloquent et logique metier si les services ne sont pas respectes
- fuite de contraintes multi-tenant si le scope tenant n'est pas centralise
- dette rapide si les stories sont trop grosses
- backend difficile a consommer si les conventions API ne sont pas stabilisees
