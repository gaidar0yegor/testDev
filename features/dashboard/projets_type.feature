Feature: Barres "Type de projets réalisés"

    Background:
        Given I have loaded fixtures from "dashboard/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user1@societe.dev  |
            | _password | user1              |
        And I press "Connexion"

    Scenario: L'API retourne les données
        When I am on "/corp/api/dashboard/projets-type/since-2018"
        Then the response should be in JSON
        And the JSON node "2018.projets" should be equal to "3"
        And the JSON node "2018.projetsRdi50" should be equal to "0"
        And the JSON node "2018.projetsRdi30" should be equal to "0"
        And the JSON node "2020.projets" should be equal to "3"
        And the JSON node "2020.projetsRdi50" should be equal to "0"
        And the JSON node "2020.projetsRdi30" should be equal to "0"

        When I am on "/corp/api/dashboard/projets-type/since-2017"
        Then the response should be in JSON
        And the JSON node "2017.projets" should be equal to "4"
        And the JSON node "2017.projetsRdi50" should be equal to "0"
        And the JSON node "2017.projetsRdi30" should be equal to "0"
        And the JSON node "2020.projets" should be equal to "3"
        And the JSON node "2020.projetsRdi50" should be equal to "0"
        And the JSON node "2020.projetsRdi30" should be equal to "0"
