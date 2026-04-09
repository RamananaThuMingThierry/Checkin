# US-024 - Creer et lister le catalogue de modules

## User Story

En tant que super-admin, je peux creer et consulter les modules vendables afin de composer les offres de maniere explicite.

## Valeur

Completer le catalogue commercial avec un referentiel de modules gere et visible, au lieu de seulement rattacher des modules existants aux offres.

## Dependances

- depend de `US-004`
- depend de `US-005`
- prepare `US-025`

## Scope backend

- endpoint de creation de module
- endpoint de listing du catalogue des modules
- validation des donnees minimales du module
- unicite du code module
- test feature de creation, listing et rejet

## Criteres d'acceptation

- un module peut etre cree dans le catalogue
- les modules peuvent etre listes
- un doublon de code est rejete clairement

## Verification

- test feature `ManageModulesCatalogTest` ajoute pour la creation et le listing
- verification du rejet sur doublon de code
