Feature: Génération d'une feuille de temps simple.

    Background:
        Given I have loaded fixtures from "admin/timesheet/simple.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"

    Scenario: L'admin peut générer une feuille de temps simple. Il a contribué sur un seul projet.
        When I follow "Feuilles de temps"
        Then I should see "Générer une feuille de temps"
        And I should see "admin@societe.dev" in the "form" element

        When I fill in the following:
            | filter_timesheet[format] | html |
        And I press "Générer"
        Then I should see "Feuille de temps" in the "h1" element
        And I should see "Novembre 2020" in the "h2" element
        And I should see "P0 Chef de projet" in the "table" element
        And I should see "Total des heures en novembre 2020 : 34.13 h"
