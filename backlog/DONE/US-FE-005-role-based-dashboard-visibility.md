# US-FE-005 - Filtrer le dashboard selon le role courant

## User Story

En tant qu utilisateur connecte, je vois un dashboard avec sidebar, header, footer et contenu limites a mon role afin de n acceder qu aux vues utiles a mon perimetre.

## Valeur

Eviter le melange entre super-admin, administration entreprise, management, RH et employes pour rendre le produit plus lisible et plus sur.

## Dependances

- depend de `US-FE-001`
- s appuie sur `US-FE-SA-001`

## Scope frontend

- layout dashboard complet avec `sidebar`, `header`, `footer` et zone `content`
- filtrage des routes et de la navigation selon le role courant
- vues dediees `Entreprises` et `Utilisateurs` pour le super-admin
- vues dediees `Entreprise` et `Utilisateurs` cote tenant selon les profils admin, manager et RH

## Criteres d acceptation

- un super-admin ne voit que les routes plateforme
- un admin entreprise voit les ecrans organisation et RH de son scope
- un manager ou un RH ne voit pas les ecrans reserves au super-admin ou a la gouvernance entreprise
- un employe ne voit qu une vue d ensemble limitee
- le header, la sidebar et le footer refletent le role et le scope courant

## Verification

- build frontend reussi
- verification manuelle du filtrage de navigation par role
