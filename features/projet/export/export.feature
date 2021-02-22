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
        Then I should see "Fait marquant un" in the "h3" element
        And I should not see "Fait marquant deux" in the "h3" element
        And I should not see "Fait marquant trois" in the "h3" element
