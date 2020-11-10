# Changelog

Tous les changements apportés au projet sont suivis dans ce fichier.

Le format est basé sur [Tenez un Changelog](https://keepachangelog.com/fr/1.0.0/).

## [À venir dans la prochaine version] - DATE
### Ajouté
- Administration front: Modification des utilisateurs
- Administration front: Désactiver ou réactiver un utilisateur
### Modifié
- Refactorisation des templates, suppression de duplications, utilise même template pour front et back office
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

## [0.1.0]  - 2020-11-01

Version initiale de l'application.
