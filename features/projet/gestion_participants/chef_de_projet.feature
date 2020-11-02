Feature: Affichage de la liste des projets de l'utilisateur

    Background:
        Given I have loaded fixtures from "projet/gestion_participants/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | cdp@societe.dev  |
            | _password | cdp              |
        And I press "Connexion"

    Scenario: Le chef de projet peut accéder à la gestion des participants.
        When I go to "/projets"
        And I follow "Projet de test"
        And I follow "Gestion des participants"
        Then I should see "Gestion des participants" in the "h1" element

    Scenario: Il doit être impossible de mettre 2 chefs de projet sur un même projet.
        When I go to "/projets"
        And I follow "Projet de test"
        And I follow "Gestion des participants"
        And I fill in the following:
            | liste_projet_participants[projetParticipants][0][role] | CDP |
        And I press "Mettre à jour"
        Then I should see "Il doit y avoir un seul Chef de projet, vous en avez plusieurs"

    Scenario: Il doit être impossible de laisser un projet sans Chef de projet.
        When I go to "/projets"
        And I follow "Projet de test"
        And I follow "Gestion des participants"
        And I fill in the following:
            | liste_projet_participants[projetParticipants][1][role] | CONTRIBUTEUR |
        And I press "Mettre à jour"
        Then I should see "Il doit y avoir un chef de projet, vous n'en avez mis aucun"
