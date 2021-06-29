# Changelog

Tous les changements apportés au projet sont suivis dans ce fichier.

Le format est basé sur [Tenez un Changelog](https://keepachangelog.com/fr/1.0.0/).

## [À venir dans la prochaine version]
### Ajouté
- Pouvoir ajouter un lien vers Trello, ou d'autres liens externes en général sur la page projet
# Réparé
- Répare bug quand on modifie un temps passé sur un projet dont on n'est plus contributeur




## [0.28.0] - 2021-06-29
### Ajouté
- Installation de la traduction, pouvoir changer la langue de son compte en Anglais
### Modifié
- Par défaut, une société créée a son nombre d'heures travaillées par jour à 7h au lieu de 7h30.
### Réparé
- Erreur d'encodage quand on génère un prénom court avec un accent comme "É. Couturier"

## [0.27.1] - 2021-06-18
### Réparé
- Erreur lorsqu'on allait sur la page de gestion des participants après une invitation par téléphone

## [0.27.0] - 2021-06-15
### Ajouté
- Ajout d'un message d'erreur explicatif dans la saisie des pourcentages de temps passés
### Modifié
- Retirer l'effet de surlignement après la création d'un fait marquant
- Réduction du nombre de charactères des SMS pour diviser le coût par 2
### Réparé
- Erreur lorsqu'on téléchargeait un fichier dont le nom contient un accent

## [0.26.0] - 2021-05-27
### Ajouté
- Back office - statistiques
    - Ajout du nombre de nouveaux utilisateurs et projets par mois
- Possibilité de saisir les pourcentages de temps à la semaine
    - L'admin peut switcher sur une saisie hebdomadaire (et revenir au mensuel)
    - Les contributeurs devront saisir les pourcentages chaque semaine (les absences ne changent pas)
    - Les feuilles de temps prennent en compte les pourcentages de chaque semaine
    - L'interface est plus réactive, installation de VueJS et utilisation d'une API pour les données

### Ajouté
- Création d'une nouvelle page dans le BO pour avoir accès à des stats
- Ajout d'un tableau avec le nombre d'user créé par mois sur l'année courante

## [0.25.1] - 2021-05-06
### Réparé
- Plusieurs petites réparations
    - Met à jour Symfony 2.5.1 => 2.5.7, et les autres dépendances php
    - N'affiche que les erreurs critique dans slack, et ignore les erreurs console
    - Affiche les rapports de cron récent au lieu des cron les plus anciens
    - Ré-affiche la page d'erreur personnalisée
    - Répare le lien du mot de passe oublié
    - Remove minimum stability to make `composer dump-env prod` works
    - Fix file upload on rackspace
    - Fix critical error `headers already sent` when downloading file
    - Fix error when running bin/console on prod env

## [0.25.0] - 2021-05-04
### Ajouté
- Dans les notifications sur le click sur le nom du fait marquant vous êtes atterissé directement sur le fait marquant sur lequel vous avez cliqué
- Création d'un onglet dashboard dans le BO pour ajouter dans le futur certaines données
- Lors d'une erreur en prod, affiche l'erreur dans un channel Slack
- Pouvoir modifier les rôles d'un utilisateur sur tous les projets

## [0.24.0] - 2021-04-27
### Ajouté
- Dans le BO ajout d'une colonne Projets pour voir le nombre de projet par société
- Ajout d'un bouton pour ajouter un fichier dans la page projet sur la side barre
- Mes absences, lors d'un changement dans le calendrier de présence, une alert s'affiche pour bien montrer que vous avez modifié celui ci.
### Modifié
- Tableau de bord : remplace "Mes tâches" et "Mes projets récents" par "Mon actualité"

### Réparé
- Répare l'erreur 500 sur la page des dernières notifications envoyées


## [0.23.0] - 2021-04-20
### Ajouté
- Observateur externe
- Si l'utilisateur courant à plus d'une société à la connexion, il est redirigé vers le switch société
### Réparé
- Corrige "Erreur serveur" lors d'un ajout de fait marquant lorsque mes licenses sont expirées
- Corrige "Erreur serveur" lorqu'une adresse email (exemple `x.@x.x`) est saisie

## [0.22.0] - 2021-04-13
### Ajouté
- Page Multi-société :
    - Affichage des notifications pour chaque société
- Permet d'utiliser RDI-Manager avec un numéro de téléphone au lieu d'un email
    - L'admin peut inviter un utilisateur avec un numéro de téléphone
    - Les utilisateurs peuvent se connecter avec leur numéro de téléphone
    - "Mot de passe oublié" possible avec son numéro de téléphone
    - Les notifications de rappel de saisie de temps/fait marquants sont envoyé par SMS si pas d'email
- Pouvoir suivre un projet pour recevoir des notifications en temps réel :
    - lorsqu'un fait marquant est créé
- Ajout d'une légende sur l'écran de saisie des absences
### Réparé
- Correction de l'invitation d'un user dans création société

## [0.21.0] - 2021-04-06
### Réparé
- Répare le click sur les lignes des tables projets et utilisateurs

## [0.20.1] - 2021-04-02
### Ajouté
- Enregistre automatiquement un brouillon sur certains formulaires :
    - Ajout/Modifie un projet
    - Ajout/Modifie un fait marquant
- Affiche une notif sur la cloche quand quelqu'un modifie notre propre fait marquant
### Modifié
- Redesign de la page projet
    - Ré-organisation des 7 boutons
    - Ajout sidebar pour les infos secondaire
### Réparé
- Multi-société :
    - L'utilisateur restait connecté lorsque son accès était désactivé : déconnecte l'user de la société lorsqu'il se réauthentifie.
    - Il était possible d'usurper l'accès d'un autre user dans la page de switch en envoyant une requete post créée à la main : ajoute un check au moment du switch
- Cliquer sur la ligne entière d'un projet pour aller dessus fonctionne à nouveau
- Notifications (cloche) : affiche les bonnes notifications après avoir switché de société

## [0.20.0] - 2021-03-30
### Ajouté
- Multi-société : avoir un accès à plusieurs sociétés avec un même compte RDI-Manager

## [0.19.0] - 2021-03-16
### Ajouté
- Back-Office : connaitre la date de création, qui et comment une société a été créée
- Pouvoir filtrer et trier la liste des projets, et des utilisateurs
- Ajoute les notifications utilisateurs (icone alarme en haut à droite) pour faire remonter dernières activités importante à l'utilisateur
    - Lorsqu'un fait marquant vient d'être créé sur un de ses projet
    - Lorsqu'il devrait mettre à jour ses temps
- Stylisation des mails fait marquants

## [0.18.0] - 2021-03-09
### Ajouté
- Systeme de licenses
    - Pouvoir en générer depuis le back-office pour des sociétés SaaS
    - Les licenses limitent le nombre de projets actif et de contributeurs dans une société
    - Les sociétés ont par défaut une license gratuite
    - L'admin peut voir ses licenses actuellement actives
    - Accès en lecture seule si la license est expirée
- Ajout des notifications d'activité, "désactiver" et "activer" les utilisateurs
- Back-office: Permettre d'ajouter un autre admin à une société
- Tableau de bord : raccourcis vers mes projets recents
### Modifié
- Ajout du CDP sur le nombre "Contributeurs" sur la vue admin d'un projet
- Statistiques d'un projet : le graphique des temps passé par users affiche maintenant un nom abrégé "P. Nom" au lieu de "Prénom Nom"
### Réparé
- Ne plus afficher "Aucun admin n'a encore recu de mail d'invitation..." quand un admin à déjà finalisé son inscription

## [0.17.0] - 2021-03-02
### Ajouté
- Affichage des observateurs ainsi que des contributeurs.
- Maj du footer
- Ajout du hover sur les tables Mes projets et Mes utilisateurs

## [0.16.0] - 2021-02-24
- Fait Marquant limité a 750 caractères (ajout d'un compteur)
### Modifié
- Modification de la vue pour le filtre de tout les projets par années (boutton, alignement du choid des dates)
### Réparé
- Rectification bug, dans les choix des dates d'export (un des deux, où les deux)

## [0.15.1] - 2021-02-19
### Ajouté
- Permettre à de nouveaux administrateurs de s'inscrire et d'ajouter leur société
- Ajout de l'activité "X à modifié le fait marquant Y sur le projet Z"

## [0.15.0] - 2021-02-17
### Ajouté
- Ajout de la selection des dates pour l'export des faits marquants
- Permettre à l'admin de connecter RDI-Manager à Slack pour envoyer les notifications de rappel de saisie des temps sur une chaîne Slack.
- Nouvelle vue admin d'un projet : affichage des temps passés par les contributeurs sur un projet

## [0.14.0] - 2021-02-09
### Ajouté
- Admin : filtre tous les projets par année
- Admin : ajout du boutton tout séléctionner/déselectionner lors de la génération des feuilles de temps
- Alignement des flèches pour le suivi des temps.
- Textes d'aide pour les nouveaux utilisateurs
- Ajout des modifications complètes pour l'administrateur
### Modifié
- Eclaircissement du formulaire d'inscription
- Onboarding :
    - Rend les 2 dernières étapes optionnelles
    - Ré-affiche l'onboarding dés la reconnexion si les 3 premières étapes ne sont pas faîtes
    - Affiche l'onboarding également pour les chefs de projets
### Réparé
- Bug quand on va sur la page de modification du nombre d'heures de la société

- Limitation dans l'ajout et la modification des faits marquants à 750 caractères. Blocage à 800 et ajout d'un compteur

## [0.13.0] - 2021-02-02 🤵
### Ajouté
- Onboarding des nouveaux administrateurs
- Admin : Graphique des % de temps passés sur les projets

### Réparé
- Ré-affiche les notifications de saisie de temps sur le dashboard

## [0.12.0] - 2021-01-26 ⛵
### Ajouté
- Ajout de la date d'entrée et de sortie d'un collaborateur
    - L'admin peut définir les dates
    - Saisie des absences : les jours où l'user n'est pas dans la société sont décochés par défaut
    - Les feuilles de temps affichent 0 heures les jours où l'user n'est pas dans la société
    - L'entrée et la sortie d'un user sont affichées dans son activité
- Permet d'ajouter du HTML configurable sur la page de login pour afficher les logins de démo
### Réparé
- Certaines années affichées (saisie temps/absences) n'étaient pas la bonne en janvier
### Supprimé
- Retire le lien d'explication du score RDI

## [0.11.0] - 2021-01-19
### Ajouté
- Affichage du score RDI d'un projet

## [0.10.2] - 2021-01-15
### Ajouté
- SEO sur la page de login :
    - Ajout des meta sur la page de login
    - Ne pas indexer les autres pages (mot de passe oublié, recommander rdi manager)
    - ajout des données opengraph, twitter card, json+ld...
    - Fix html invalide
- Historique d'activité sur les projets et utilisateurs :
    - X a ajouté le fait marquant Y sur le projet Z
    - X a créé le projet Y

## [0.10.1] - 2021-01-14
*Rien de nouveau, test du script de déploiement*

## [0.10.0] - 2021-01-12 🍫
### Ajouté
- Mail de recommandation :
    - zone de texte personnalisée dans l'email
    - ajout du lien vers la vidéo de présentation sur youtube
### Modifié
- Page d'invitation sur projet : affiche l'acronyme du projet dans le titre au lieu du titre
### Réparé
- Ré-affiche les icônes sur la page de login

## [0.9.0] - 2021-01-05 ⛄
### Ajouté
- Admin : affiche les projets dont un utilisateur participe
- Exporter les feuilles de temps au format Excel
- Vue admin des temps saisis de tous les collaborateurs pour vérifier si tout le monde est à jour
### Modifié
- Dashboard, demande de mettre à jour les temps passés avec un délai de 20 jours

## [0.8.0] - 2020-12-22 🎅
### Ajouté
- Intégration de Matomo pour suivre les actions des visiteurs sur le site
- Back office : Initialiser une nouvelle société avec son administrateur
- Ajout des liens vers twitter, linkedin, fb, eurekaci.com dans le footer
- Permettre aux utilisateurs de recommander RDI-Manager
- Notification de rappel des temps saisis également par SMS
### Modifié
- Migration vers Symfony 5
### Réparé
- Les données des graphiques du tableau de bord sont maintenant plus cohérentes

## [0.7.0] - 2020-12-15
### Ajouté
- Page de mes projets : ajout d'un filtre des projets actifs par année
### Réparé
- Réparation du bug de noël

## [0.6.2] - 2020-12-11
### Ajouté
- L'administrateur peut paramétrer le jour et l'heure d'envoi des différentes notifications
- Jeu de données aléatoire pour faire remonter des données en démo
- Fixtures : projet Fake : Stat Planete : https://www.statsilk.com/timeline-and-release-notes#2015

## [0.6.1] - 2020-12-09
### Réparé
- La page de création des faits marquants fonctionne de nouveau

## [0.6.0] - 2020-12-09
### Ajouté
- Permet de joindre un fichier à un fait marquant
- Commande pour envoyer un mail aux utilisateurs pour leur rappeller de saisir leurs temps
- Possibilité d'exporter en Pdf la fiche d'un projet avec l'ensemble de ses faits marquants
### Modifié
- Refactorise l'entité FichierProjet dans Fichier pour être plus flexible sur les fichiers
- Utilise flysystem comme couche d'abstraction du système de fichier
- Déplace `public/upload` vers `var/storage/default/upload` pour ne plus exposer les fichiers publiquement

## [0.5.2] - 2020-12-03
### Modifié
- Page de connexion revue

## [0.5.1] - 2020-12-03
### Ajouté
- Tableau de bord : tableau "Moi VS Équipe"
- Tableau de bord : Camembert "Réalisation des projets"
- Tableau de bord : Barres "Type de projets réalisés"
### Modifié
- Tableau de bord : les graphiques affichent les données seulement pour les projets dont je suis observateur, ou tous les projets si je suis admin
- Tableau de bord : Réduit la taille de la notif des temps passés à jour en remplacant par une simple alerte
### Réparé
- Fix: Admin doit pouvoir créer des ressources sur tous les projets
### Supprimé
- Suppression du champ en base de données `projet.statut_rdi` car non utilisé

## [0.5.0] - 2020-12-02
### Ajouté
- Commande pour notifier les utilisateurs d'ajouter leur faits marquants
- Commande pour envoyer les nouveaux faits marquants de la semaine sur mes projets
- `.env`: ajout de `REQUEST_BASE_HOST` à remplir avec le host de l'application pour mettre la bonne url dans les emails envoyés depuis la commande.
### Modifié
- Renomination des rôles dans le fichier messages.fr/yaml, affichage en mode responsive de la liste des utilisateurs
- Tableau de bord : retire message indiquant de remplir ses absences du mois car non pertinent
- Email de test : ajout de l'url de l'application pour vérifier si la config de `REQUEST_BASE_HOST` est bonne.

## [0.4.1] - 2020-11-27
### Ajouté
- Menu Administration : ajout du menu "Tous les projets" qui Liste de l'ensemble des projets de la societe
- Modification de mes données personnelles
- Affiche les dernières dates de mise à jour des temps passés et absences
- Tableau de bord : Dire si l'utilisateur est à jour, ou doit encore faire une action pour se mettre à jour dans la saisie de ses temps et absences du mois courant.
- Tableau de bord : Graphique des heures par projet
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
