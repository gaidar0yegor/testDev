Feature: Affichage de la liste des projets de l'utilisateur

    Background:
        Given I have loaded fixtures from "projet/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

    Scenario: Un utilisateur peut voir les projets sur lesquels il participe
        When I go to "/projets/tous-les-projets"
        Then I should see "P1"
        And I should see "P3"
        But I should not see "P2"

    Scenario: Un utilisateur peut voir quels rôle il a sur ses projets
        When I go to "/projets/tous-les-projets"
        Then I should see "Contributeur" in the "P1" row
        Then I should see "Observateur" in the "P3" row

    Scenario: Un utilisateur peut afficher les projets actifs seulement une année précise
        When I go to "/projets/tous-les-projets"
        Then I should see "P1"
        And I should see "P4"
        And I should see a "#projets-year-filter" element

        When I go to "/projets/2020"
        Then I should see "P1"
        But I should not see "P4"
