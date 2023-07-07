Feature: L'admin peut avoir un oeil global sur la saisie des temps passés de tout le monde

    Background:
        Given I have loaded fixtures from "admin/cra_validations/fixtures.yml"

    Scenario: L'admin peut voir qui n'est pas à jour dans la saisie de ses temps passés
        Given I am on "/connexion"
        And I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"

        When I follow "Validation des temps"
        Then I should see "Validation des temps" in the "h1" element

        When I go to "/corp/admin/validations/2020"
        Then I should see "Validation des temps de 2020" in the "h1" element
        And I should see "User 1 Eureka" in the "table" element
        And I should see a "table tbody td span.text-success" element
        And I should see a "table tbody td span.text-danger" element
        And I should see a "table tbody td span.text-grey" element
