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
        When I go to "/corp/admin/utilisateurs"
        Then the response status code should be 403
        And I should not see "Liste des utilisateurs"

    Scenario: L'utilisateur peut accéder à la page publique d'un autre utilisateur
        When I go to "/corp/utilisateur/1"
        Then the response status code should be 200
        And I should see "Admin Eureka" in the "h1" element
        And I should see "Rôle"
        But I should not see "Mettre à jour"
        And I should not see "Téléphone"

    Scenario: L'utilisateur ne doit pas pouvoir modifier les infos d'un utilisateur
        When I go to "/corp/utilisateur/1/modifier"
        Then the response status code should be 403
        And I should not see "Modification de"

    Scenario: L'utilisateur peut accéder à la page publique d'un autre utilisateur
        When I go to "/corp/utilisateur/1"
        Then I should not see "Temps passés en %"
