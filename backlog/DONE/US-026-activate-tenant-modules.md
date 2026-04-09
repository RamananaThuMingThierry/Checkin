# US-026 - Activer les modules du tenant depuis l'abonnement

## User Story

En tant que systeme, je peux activer les modules du tenant a partir de son abonnement afin de piloter l'acces fonctionnel reel.

## Valeur

Relier le catalogue commercial aux droits fonctionnels effectivement disponibles pour le tenant.

## Dependances

- depend de `US-025`
- prepare les futurs controles d'acces fonctionnels et le frontend

## Scope backend

- projection des modules de l'offre vers le tenant
- persistance dans `tenant_modules`
- prevention du doublon d'activation
- endpoint de listing des modules actifs d'un tenant
- test feature d'activation et de listing

## Criteres d'acceptation

- les modules d'une offre peuvent etre actives pour un tenant
- un module n'est pas active deux fois
- le tenant peut lister ses modules actifs

## Verification

- test feature `ActivateTenantModulesTest` ajoute pour l'activation et le listing
- verification de la prevention du doublon d'activation
