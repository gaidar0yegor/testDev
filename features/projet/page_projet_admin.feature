Feature: Affichage de la page d'un projet en tant qu'admin

    Background:
        Given I have loaded fixtures from "projet/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"

    Scenario: Un admin peut tout faire sur un projet de sa société même s'il n'est que observateur
        When I go to "/corp/projets/1"
        Then I should see "P1" in the "h1" element

        When I follow "Gérer les participants"
        Then I should see "Gestion des participants" in the "h1" element

        When I go to "/corp/projets/1/modifier"
        Then I should see "Edition du projet P1"

    Scenario: Un admin peut tout faire sur un projet de sa société même s'il n'y participe pas
        When I go to "/corp/projets/2"
        Then I should see "P2" in the "h1" element

        When I follow "Gérer les participants"
        Then I should see "Gestion des participants" in the "h1" element

        When I go to "/corp/projets/2/modifier"
        Then I should see "Edition du projet P2"
