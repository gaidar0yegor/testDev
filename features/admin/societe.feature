Feature: Le référent peut voir les infos de sa société et les modifier.

    Background:
        Given I have loaded fixtures from "admin/users.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"

    Scenario: Le référent peut voir les infos de sa société
        When I follow "Ma société"
        Then I should see "SociétéTest" in the "h1" element

    Scenario: Le référent peut définir un nombre d'heure travaillées par jour par défaut
        When I follow "Ma société"
        Then I should see "À définir" in the ".card" element containing "Heures travaillées"

        When I follow "Modifier le nombre d'heures"
        And I fill in the following:
            | societe[heuresParJours] | 7.5 |
        And I press "Mettre à jour"
        Then I should see "7.5 h" in the ".card" element containing "Heures travaillées"
