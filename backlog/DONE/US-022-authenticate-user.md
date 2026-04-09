# US-022 - Authentifier un utilisateur plateforme ou tenant

## User Story

En tant qu'utilisateur plateforme ou tenant, je peux m'authentifier afin d'acceder aux APIs selon mon role.

## Valeur

Poser le point d'entree de securite necessaire avant toute consommation reelle du backend par le frontend React.

## Dependances

- depend de `US-001`
- depend de `US-002`
- depend de `US-003`
- depend de `US-008`
- prepare `US-023`

## Scope backend

- endpoint de login par email et mot de passe
- emission d'un token d'API
- rejection claire des identifiants invalides
- trace minimale du dernier login
- test feature du login nominal et en erreur

## Criteres d'acceptation

- un utilisateur valide peut obtenir un token
- un utilisateur invalide est rejete clairement
- le contexte de securite est exploitable par les futurs endpoints proteges

## Verification

- test feature `AuthenticateUserTest` ajoute pour le login nominal
- verification des rejets sur identifiants invalides, compte inactif et payload incomplet
