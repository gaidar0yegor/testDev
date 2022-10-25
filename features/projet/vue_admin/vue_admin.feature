Feature: Affichage du score RDI

    Background:
        Given I have loaded fixtures from "projet/vue_admin/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"

    Scenario: L'admin peut voir le nombre de contributeurs, de faits marquants et les temps passés sur le projet
        Given I am on "/corp/projets"
        And I follow "PTEST"
        When I follow "Statistiques"
        Then I should see "Contributeurs"
        And I should see "Faits marquants"
        And I should see "Temps passé en"
