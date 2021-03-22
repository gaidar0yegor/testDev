# MultiSociete

Permet aux utilisateurs d'avoir un accès sur plusieurs sociétés
avec un seul compte RDI-Manager.

## Description

Dans le cas où un utilisateur souhaite suivre plusieurs société,
il doit pouvoir, avec sa même adresse email, avoir un accès sur plusieurs
sociétés, avec probablement des rôles différents (admin sur une société,
chef de projet sur une autre).

## Technique

Entities:
    - User: représente le compte RDI-Manager d'un utilisateur
        - nom / prénom
        - informations de connexion
        - currentSocieteUser: le societeUser pour indiquer sur quelle société l'user à switché en ce moment. Si null, l'user sera redirigé sur la page de switch si besoin.
    - SocieteUser: accès d'un user sur une société avec un rôle, et contient les info de l'user relative à la société, comme :
        - user: l'user qui a l'accès
        - societe: la société sur laquelle il a accès
        - dateEntree/dateSortie: dates à laquelle il a rejoint officiellement la société (sert pour le calcul des feuilles de temps)
        - invitationToken/invitationEmail: lien d'invitation sur cette société, envoyé à cet email
