Feature: Connexion au back office

    Scenario: Je dois pouvoir accéder au back office
        Given I have loaded fixtures from "back_office/connexion.yml"
        And I am on "/connexion"
        When I fill in the following:
            | _username | user@bo.dev |
            | _password | user        |
        And I press "Connexion"
        Then I should see "Back office | User"
        And I should see "Back office" in the ".navbar-brand" element
