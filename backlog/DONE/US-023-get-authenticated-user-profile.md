# US-023 - Exposer le profil de l'utilisateur authentifie

## User Story

En tant qu'utilisateur connecte, je peux recuperer mon profil courant afin que le frontend connaisse mon identite, mon tenant et mes roles.

## Valeur

Permettre au frontend de construire l'experience apres login sans multiplier les appels metier implicites.

## Dependances

- depend de `US-022`
- prepare les futurs endpoints proteges

## Scope backend

- endpoint `me` protege par authentification
- retour des informations de base utilisateur
- retour du tenant et des roles utiles au frontend
- rejection claire si le token est absent ou invalide
- test feature d'acces autorise et non autorise

## Criteres d'acceptation

- un utilisateur authentifie peut lire son profil
- la reponse expose le contexte minimal d'acces
- un acces anonyme est rejete clairement

## Verification

- test feature `GetAuthenticatedUserProfileTest` ajoute pour l'acces autorise
- verification des rejets sans token et avec token invalide
