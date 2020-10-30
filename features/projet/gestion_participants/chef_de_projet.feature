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
