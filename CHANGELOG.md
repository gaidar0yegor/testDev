# Changelog

Tous les changements apportés au projet sont suivis dans ce fichier.

Le format est basé sur [Tenez un Changelog](https://keepachangelog.com/fr/1.0.0/).

## [À venir dans la prochaine version] - DATE
### Ajouté
### Modifié
### Réparé

## [0.2.0] - 2020-11-12
### Ajouté
- Administration front: Modification des utilisateurs
- Administration front: Désactiver ou réactiver un utilisateur
- Projet: Un chef de projet peut inviter des nouveaux utilisateur sur RDI manager, sur son projet
- Mettre un background pour le cadre (gris clair). Idée : harmoniser les contenus, les centrer sur la page web.
### Modifié
- Refactorisation des templates, suppression de duplications, utilise même template pour front et back office
- Agrandir la zone de saisie des faits marquants à 5lignes ou plus
- texte du résumé justifié, items et descritpifs alignés sur deux colonnes
- correspondances des tags mis à jour

- Agencement des éléments : réduire ou agrandir le titre en fonction du cadre. Dans le cadre, items et respectifs doivent être
  alignés (Idem pour la page Mon compte). Pareil pour la timeline "Faits marquants", elle doit être alignée sur le cadre.
### Déprécié
### Supprimé

### Réparé
- Embarque les css/js dans le projet, n'utilise plus de CDN tierces
- Retire tous les css/js du html et centralise tout dans styles.css
### Sécurité
- Liste utilisateurs: ne pas afficher les utilisateurs des autres sociétés

## [0.1.1] - 2020-11-09
### Ajouté
- Commande `app:init-societe-referent` pour ajouter une nouvelle société et son référent plus facilement
- Affichage de la version actuelle de l'application dans le footer
### Modifié
- Fiche projet : L'acronyme et titre sont inversés
- Edition projet : Ajout de plus d'espace dans le champ description
### Réparé
- Affichage du message "Lien d'invitation invalide ou expiré" pour que l'utilisateur comprenne qu'il peut maintenant s'inscrire
- Affichage d'un message clair lors d'une erreur 4xx général

### Modifié
- Fiche projet : Intitulé des boutons : Fichiers joints  --> Fichiers
- Fiche projet : Gestion des participants --> Participants
- Fiche projet : Ajouter un fait marquant --> Fait marquant
- Fiche projet : Modifier le projet --> Modifier Projet
- Fiche projet : Afficher titre descriptif selon la même mise en page que pour les         catégories "résumé" , Chef de projet..
- Fiche projet : Justifier les informations générales du projet

- Infos projet :  Dans le formulaire Remplacer Acronyme par Titre réduit 
                  Remplacer Titre par Titre descriptif
### Supprimé
- Fiche projet : Retirer bouton "Ajouter un fichier"


## [0.1.0]  - 2020-11-01

Version initiale de l'application.
