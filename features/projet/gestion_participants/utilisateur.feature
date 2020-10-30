Feature: Affichage de la liste des projets de l'utilisateur

    Background:
        Given I have loaded fixtures from "projet/gestion_participants/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

    Scenario: Il faut avoir le rôle Chef de projet pour gérer les participants.
        When I go to "/projets"
        And I follow "Projet de test"
        And I follow "Gestion des participants"
        Then the response status code should be 403
        And I should not see "Gestion des participants" in the "h1" element
