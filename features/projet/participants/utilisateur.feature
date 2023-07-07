Feature: Affichage de la liste des projets de l'utilisateur

    Background:
        Given I have loaded fixtures from "projet/participants/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

    Scenario: Il faut avoir le rôle Chef de projet pour gérer les participants.
        When I go to "/corp/projets"
        And I follow "P"
        Then I should not see "Gérer les participants"

        When I go to "/corp/projets/1/participants"
        Then the response status code should be 403
