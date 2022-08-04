Feature: Graphique "Heures par projets"

    Background:
        Given I have loaded fixtures from "dashboard/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user1@societe.dev  |
            | _password | user1              |
        And I press "Connexion"

    Scenario: L'API retourne les données
        When I am on "/corp/api/dashboard/heures-par-projet/2020"
        Then the response should be in JSON
        And the JSON node "userProjetsHeuresPassees" should have 3 elements
        And the JSON node "userProjetsHeuresPassees.P1" should be equal to "68.25"
        And the JSON node "userProjetsHeuresPassees.P2" should be equal to "54.60000000000001"
        And the JSON node "userProjetsHeuresPassees.P3" should be equal to "40.94999999999999"
