Feature: Camembert "Réalisation des projets"

    Background:
        Given I have loaded fixtures from "dashboard/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user1@societe.dev  |
            | _password | user1              |
        And I press "Connexion"

    Scenario: L'API retourne les données
        When I am on "/corp/api/dashboard/projets-statuts/since-2018"
        Then the response should be in JSON
        And the JSON node "active" should be equal to "3"
        And the JSON node "finished" should be equal to "0"

        When I am on "/corp/api/dashboard/projets-statuts/since-2017"
        Then the response should be in JSON
        And the JSON node "active" should be equal to "3"
        And the JSON node "finished" should be equal to "1"
