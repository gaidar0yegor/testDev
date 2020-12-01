# Changelog

Tous les changements apportés au projet sont suivis dans ce fichier.

Le format est basé sur [Tenez un Changelog](https://keepachangelog.com/fr/1.0.0/).

## [À venir dans la prochaine version] - 2020-12-01
- Renomination des rôles dans le fichier messages.fr/yaml, affichage en mode responsive de la liste des utilisateurs
### Ajouté
### Modifié
### Réparé
### Supprimé



## [0.4.1] - 2020-11-27
### Ajouté
- Modification de mes données personnelles
- Affiche les dernières dates de mise à jour des temps passés et absences
- Tableau de bord : Dire si l'utilisateur est à jour, ou doit encore faire une action pour se mettre à jour dans la saisie de ses temps et absences du mois courant.
- Tableau de bord : Graphique des heures par projet
### Modifié
### Réparé
- Pouvoir supprimer mon propre fichier même si je ne suis pas chef de projet
- Dans la liste des fichiers, le bouton supprimer est grisé si je n'ai pas le droit de supprimer
- Dans la liste des fichiers, le bouton "Ajouter un fichier" est grisé si on n'est pas contributeur
### Supprimé
- Le champ Projet->statut a été retiré car non utilisé et peut prêter à confusion

## [0.4.0] - 2020-11-24
### Ajouté
- Ré-intégration des migrations doctrine pour mettre à jour la base de prod plus facilement
### Modifié
- Feuilles de temps : prend en compte date de début et de fin de projet
- Saisis des temps : ne permet plus de saisir un temps sur un projet fini ou pas commencé
### Réparé

## [0.3.1] - 2020-11-20
### Ajouté
- Ajout d'éléments "Actif" et "Inactif" dans la colonne "Statut" de la liste des utilisateurs
- Mot de passe oublié
- Changement mot de passe
### Modifié
- Refonte du menu de navigation du haut
- Sélecteur de mois de l'export des feuilles de temps refait
- Boutons bootstrap partout
### Réparé
- Réaligne le footer

## [0.3.0] - 2020-11-18
### Ajouté
- texte du résumé justifié, items et descritpifs alignés sur deux colonnes
- Upload de fichiers sur un projet
- correspondances des tags mis à jour
- Remplacer le message "Saisissez vos congés si vous en avez pris ce mois ci" par  "Saisissez vos absences si vous en avez eu ce mois ci."
- Liste des utilisateurs(FO): ajouter les colonnes "Rôles" et "Statut", enlever "Prénom" et "Email" en mode responsive
- Agencement des éléments : réduire ou agrandir le titre en fonction du cadre. Dans le cadre, items et respectifs doivent être
  alignés (Idem pour la page Mon compte). Pareil pour la timeline "Faits marquants", elle doit être alignée sur le cadre.
- Ajout d'éléments "Actif" et "Inactif" dans la colonne "Statut" de la liste des utilisateurs
- Réorganisation en mode statique du tableau de bord

## [0.2.1] - 2020-11-17
### Ajouté
- Ajoute tooltip en JS pour meilleur support mobile, et fonctionne aussi sur les elements disabled
- Administration front: Génération des feuilles de temps
- Saisie des congés : les jours fériés sont maintenant grisés par défaut
- (Header) Ajouter le nom de la société doit etre affiché en dessous du nom de l'utilisateur.
### Modifié
- Saisie des temps passés : flèche grisée pour ne pas aller dans un mois futur
- Saisie des temps passés : Affichage de l'acronyme plutôt que le titre descriptif pour réduire la taille du formulaire
- Mise à jour du logo header et page connexion
### Réparé
- Modification projet : Les dates de début et de fin ne se mettent plus à la date du jour toutes seules
- Responsive de la page de connexion

## [0.2.0] - 2020-11-12
### Ajouté
- Administration front: Modification des utilisateurs
- Administration front: Désactiver ou réactiver un utilisateur
- Projet: Un chef de projet peut inviter des nouveaux utilisateur sur RDI manager, sur son projet
- Mettre un background pour le cadre (gris clair). Idée : harmoniser les contenus, les centrer sur la page web.
### Modifié
- Refactorisation des templates, suppression de duplications, utilise même template pour front et back office
- Agrandir la zone de saisie des faits marquants à 5lignes ou plus
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
