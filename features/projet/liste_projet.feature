Feature: Affichage de la liste des projets de l'utilisateur

    Background:
        Given I have loaded fixtures from "projet/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

    Scenario: Un utilisateur peut voir les projets sur lesquels il participe
        When I go to "/projets"
        Then I should see "Projet 1"
        And I should see "Projet 3"
        But I should not see "Projet 2"

    Scenario: Un utilisateur peut voir quels rôle il a sur ses projets
        When I go to "/projets"
        Then I should see "Contributeur" in the "Projet 1" row
        Then I should see "Observateur" in the "Projet 3" row
