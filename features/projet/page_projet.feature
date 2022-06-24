Feature: Affichage de la liste des projets de l'utilisateur

    Background:
        Given I have loaded fixtures from "projet/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

    Scenario: Un utilisateur ne peut pas voir un projet dont il n'a aucun rôle
        When I go to "/corp/projets/2"
        Then the response status code should be 403
