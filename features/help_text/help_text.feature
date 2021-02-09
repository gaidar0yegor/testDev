Feature: Saisie des temps passés en pourcentage sur les projets dont l'utilisateur participe.

    Background:
        Given I have loaded fixtures from "help_text/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

    Scenario: L'utilisateur doit voir le message d'aide la première fois, et pouvoir le fermer définitivement
        When I follow "Temps passés"
        Then I should see "Vous pouvez ici saisir en pourcentage le temps que vous avez passé en moyenne sur vos projets dont vous avez contribué dans ce mois"

        When I send a POST request to "/api/help-text/acknowledge" with body:
            """
            {"helpId":"saisieTempsPasse"}
            """
        And I go to "/temps"
        Then I should not see "Vous pouvez ici saisir en pourcentage le temps que vous avez passé en moyenne sur vos projets dont vous avez contribué dans ce mois"
