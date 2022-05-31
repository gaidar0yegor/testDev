Feature: Génération d'une feuille de temps simple.

    Background:
        Given I have loaded fixtures from "admin/timesheet/simple.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"

    Scenario: L'admin peut générer une feuille de temps simple. Il a contribué sur un seul projet.
        When I follow "Export feuilles de temps"
        Then I should see "Export feuilles de temps"
        And I should see "Tout sélectionner"

        When I fill in the following:
            | À partir de              | novembre 2020 |
            | Jusqu'au                 | novembre 2020 |
            | filter_timesheet[format] | html          |
        And I press "Exporter au format"
        Then I should see "Feuille de temps" in the "h1" element
        And I should see "Novembre 2020" in the "h2" element
        And I should see "P0 Chef de Projet" in the "table" element
        And I should see "Total des heures en novembre 2020 : 34.13 h"
