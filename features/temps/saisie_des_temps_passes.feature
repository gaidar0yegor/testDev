Feature: Saisie des temps passés en pourcentage sur les projets dont l'utilisateur participe.

    Background:
        Given I have loaded fixtures from "temps/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

    Scenario: L'utilisateur peut remplir ses temps passés, et doit pouvoir continuer sur ses absences.
        When I follow "Temps passés"
        Then I should see "Saisie de temps passé"

        When I fill in the following:
            | Projet P1 | 10 |
            | Projet P2 | 20 |
        And I press "Mettre à jour"
        Then I should see "Temps passés mis à jour"
        And I should see "Saisissez vos absences si vous en avez pris ce mois ci"

        When I follow "Saisissez vos absences"
        Then I should see "Indiquez vos jours d'absence"

    Scenario: L'utilisateur ne peut pas saisir un pourcentage > 100%
        Given I am on "/temps"

        When I fill in the following:
            | Projet P1 | 105 |
            | Projet P2 | 20  |
        And I press "Mettre à jour"
        Then I should see "Un pourcentage doit être entre 0 et 100, 105 obtenu"

    Scenario: La somme des pourcentages saisis sur un même mois ne peut pas dépasser 100%
        Given I am on "/temps"

        When I fill in the following:
            | Projet P1 | 60 |
            | Projet P2 | 55 |
        And I press "Mettre à jour"
        Then I should see "La somme des pourcentages doit être entre 0 et 100, 115 obtenu"

    Scenario: Je ne vois que les projets qui sont déjà commencés, ou qui commencent ce mois ci
        When I am on "/temps/2020/02"
        Then I should not see "Projet P3" in the "form" element

        When I am on "/temps/2020/03"
        Then I should see "Projet P3" in the "form" element

        When I am on "/temps/2020/04"
        Then I should see "Projet P3" in the "form" element

    Scenario: Je ne vois que les projets qui ne sont pas encore terminés
        When I am on "/temps/2020/02"
        Then I should see "Projet P4" in the "form" element

        When I am on "/temps/2020/03"
        Then I should see "Projet P4" in the "form" element

        When I am on "/temps/2020/04"
        Then I should not see "Projet P4" in the "form" element
