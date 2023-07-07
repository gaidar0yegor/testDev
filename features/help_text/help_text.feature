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
        Then I should see "Vous pouvez saisir ici le temps que vous avez passé en moyenne sur les projets pour lesquels vous avez contribué durant cette période"

        When I send a POST request to "/corp/api/help-text/acknowledge" with body:
            """
            {"helpId":"saisieTempsPasse"}
            """
        And I go to "/corp/temps"
        Then I should not see "Vous pouvez saisir ici le temps que vous avez passé en moyenne sur les projets pour lesquels vous avez contribué durant cette période"
