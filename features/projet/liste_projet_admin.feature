Feature: Affichage de la liste des projets de l'utilisateur

    Background:
        Given I have loaded fixtures from "projet/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"

    Scenario: L'admin voit le raccourcis vers tous les projets de la société dans la liste des projets auxquels il participe.
        When I go to "/corp/projets"
        Then I should see "En tant qu'administrateur, je peux aussi accéder à tous les projets de la société"

        When I follow "accéder à tous les projets de la société"
        Then I should be on "/corp/admin/tous-les-projets"
