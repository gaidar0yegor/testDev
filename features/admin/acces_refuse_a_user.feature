Feature: Il faut être référent pour pouvoir gérer les utilisateurs.
    L'accès aux pages admin est refusé aux autres utilisateurs.

    Background:
        Given I have loaded fixtures from "admin/users.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

    Scenario: L'utilisateur ne doit pas avoir accès à la liste des utilisateurs
        When I go to "/utilisateurs"
        Then the response status code should be 403
        And I should not see "Liste des utilisateurs"

    Scenario: L'utilisateur ne doit pas avoir accès aux données d'un utilisateur
        When I go to "/utilisateurs/1"
        Then the response status code should be 403
        And I should not see "Compte de"

    Scenario: L'utilisateur ne doit pas pouvoir modifier les infos d'un utilisateur
        When I go to "/utilisateurs/1/modifier"
        Then the response status code should be 403
        And I should not see "Modification de"
