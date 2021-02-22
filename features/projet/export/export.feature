Feature: Gestion des faits marquants d'un projet

    Background:
        Given I have loaded fixtures from "projet/export/fixtures.yml"

    Scenario: Un contributeur peut créer générer une page html
        Given I am on "/connexion"
        And I fill in the following:
            | _username | contributeur@societe.dev  |
            | _password | contributeur              |
        And I press "Connexion"
        And I am on "/projets/1"

        When I follow "Export PDF"
        Then I should see "Date de début"
        And I should see "Date de fin"

        When I fill in the following:
            | Date de début         | 01 février 2021 |
            | Date de fin           | 28 février 2021 |
            | projet_export[format] | html            |
        And I press "Générer"