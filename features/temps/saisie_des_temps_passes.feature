Feature: Saisie des temps passés en pourcentage sur les projets dont l'utilisateur participe.

    Background:
        Given I have loaded fixtures from "temps/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

    Scenario: L'utilisateur peut remplir ses temps passés, et doit pouvoir continuer sur ses absences.
        When I follow "Feuille de temps"
        Then I should see "Saisie de temps passé"

        When I fill in the following:
            | temps_passes[tempsPasses][0][pourcentage] | 10 |
            | temps_passes[tempsPasses][1][pourcentage] | 20 |
        And I press "Mettre à jour"
        Then I should see "Temps passés mis à jour"
        And I should see "Saisissez vos absences si vous en avez pris ce mois ci"

        When I follow "Saisissez vos absences"
        Then I should see "Indiquez vos jours d'absence"

    Scenario: L'utilisateur ne peut pas saisir un pourcentage > 100%
        Given I am on "/temps"

        When I fill in the following:
            | temps_passes[tempsPasses][0][pourcentage] | 105 |
            | temps_passes[tempsPasses][1][pourcentage] | 20  |
        And I press "Mettre à jour"
        Then I should see "Un pourcentage doit être entre 0 et 100, 105 obtenu"

    Scenario: La somme des pourcentages saisis sur un même mois ne peut pas dépasser 100%
        Given I am on "/temps"

        When I fill in the following:
            | temps_passes[tempsPasses][0][pourcentage] | 60 |
            | temps_passes[tempsPasses][1][pourcentage] | 55 |
        And I press "Mettre à jour"
        Then I should see "La somme des pourcentages doit être entre 0 et 100, 115 obtenu"
