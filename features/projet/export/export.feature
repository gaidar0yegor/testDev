Feature: Gestion des faits marquants d'un projet

    Background:
        Given I have loaded fixtures from "projet/export/fixtures.yml"
        Given I am on "/connexion"
        And I fill in the following:
            | _username | cdp@societe.dev  |
            | _password | cdp              |
        And I press "Connexion"
        And I am on "/projets/1"

        When I follow "Export PDF"
        Then I should see "Date de début"
        And I should see "Date de fin"

    Scenario: Un chef de projet peut  générer une page html avec les deux champs

        When I fill in the following:
            | Date de début         | 01 février 2021 |
            | Date de fin           | 28 février 2021 |
            | projet_export[format] | html            |
        And I press "Générer"
        Then I should see "Fait marquant deux"
        And I should not see "Fait marquant un"
        And I should not see "Fait marquant trois"

    Scenario: Un chef de projet peut générer une page html avec le premier champs vide et le deuxieme vide

        When I fill in the following:
            | Date de début         |                 |
            | Date de fin           | 28 février 2021 |
            | projet_export[format] | html            |
        And I press "Générer"
        Then I should see "Fait marquant un"
        And I should see "Fait marquant deux"
        And I should not see "Fait marquant trois"

    Scenario: Un chef de projet peut  générer une page html avec le premier remplis et le deuxieme vide

        When I fill in the following:
            | Date de début         | 01 février 2021 |
            | Date de fin           |                 |
            | projet_export[format] | html            |
        And I press "Générer"
        Then I should not see "Fait marquant un"
        And I should see "Fait marquant deux"
        And I should see "Fait marquant trois"

    Scenario: Un chef de projet peut générer toute une page html

        When I fill in the following:
            | Date de début         |                 |
            | Date de fin           |                 |
            | projet_export[format] | html            |
        And I press "Générer"
        Then I should see "Fait marquant un"
        And I should see "Fait marquant deux"
        And I should see "Fait marquant trois"