Feature: Liste des utilisateurs visible par le référent.

    Background:
        Given I have loaded fixtures from "admin/users.yml"

        Given I am on "/connexion"
        When I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"

    Scenario: L'admin peut voir la liste de ses utilisateurs
        When I follow "Utilisateurs"
        Then I should see "Liste des utilisateurs" in the "h1" element
        And I should see "admin@societe.dev" in the "table" element
        And I should see "user@societe.dev" in the "table" element

    Scenario: L'admin ne doit pas voir les utilisateurs des autres sociétés
        When I follow "Utilisateurs"
        Then I should see "user@societe.dev" in the "table" element
        But I should not see "autre-user@societe.dev" in the "table" element
