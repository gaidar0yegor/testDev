Feature: Tableau "Moi VS Equipe"

    Background:
        Given I have loaded fixtures from "dashboard/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user1@societe.dev  |
            | _password | user1              |
        And I press "Connexion"

    Scenario: L'API retourne les données
        When I am on "/corp/api/dashboard/moi-vs-equipe/2020"
        Then the response should be in JSON
        And the JSON node "projets.moi" should be equal to "2"
        And the JSON node "projets.equipe" should be equal to "3"
        And the JSON node "projetsRdi.moi" should be equal to "0"
        And the JSON node "projetsRdi.equipe" should be equal to "0"
        And the JSON node "tempsTotal.moi" should be equal to "27.29999999999999"
        And the JSON node "tempsTotal.equipe" should be equal to "163.8"
