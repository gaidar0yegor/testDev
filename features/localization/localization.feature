Feature: Traduction de l'application

    Background:
        Given I have loaded fixtures from "localization/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

    Scenario: Je peux changer la langue
        When I go to "/corp/mes-societes"
        And I follow "Mon compte"
        Then I should see "Mon compte" in the "h1" element
        And I should see "Langue Français"

        When I follow "Mettre à jour"
        When I fill in the following:
            | mon_compte[locale] | en |
        And I press "Mettre à jour"
        Then I should see "My account" in the "h1" element
        And I should see "Language English"
