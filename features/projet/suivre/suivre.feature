Feature: Pouvoir suivre un projet pour recevoir des notifications en temps réel

    Background:
        Given I have loaded fixtures from "projet/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

    Scenario: Un utilisateur peut suivre un projet
        When I go to "/corp/projets/1"
        Then I should see "Suivre le projet"
        And I should see a ".watch-projet .btn-outline-primary" element

        When I send a POST request to "/corp/api/projet/1/watch"
        And the response status code should be 204
        And I go to "/corp/projets/1"
        Then I should see "Suivre le projet"
        And I should see a ".watch-projet .btn-primary" element

        When I send a POST request to "/corp/api/projet/1/unwatch"
        And the response status code should be 204
        And I go to "/corp/projets/1"
        Then I should see "Suivre le projet"
        And I should see a ".watch-projet .btn-outline-primary" element
