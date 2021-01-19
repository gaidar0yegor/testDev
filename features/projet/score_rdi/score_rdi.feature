Feature: Affichage du score RDI

    Background:
        Given I have loaded fixtures from "projet/score_rdi/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | cdp@societe.dev  |
            | _password | cdp              |
        And I press "Connexion"

    Scenario: Rien n'apparaît si le score n'as pas été calculé, ou défini à null
        Given I am on "/projets"
        And I follow "P_null"
        Then I should not see "Score RDI"

    Scenario: Je vois le score RDI
        Given I am on "/projets"
        And I follow "P_rdi"
        Then I should see "Score RDI"
        And I should see "75" in the ".progress-bar" element
        And I should see "Score fiable à 98 %"

    Scenario: Le score RDI n'est pas indiqué tant qu'il n'est pas encore fiable
        Given I am on "/projets"
        And I follow "P_unreliable"
        Then I should see "Score RDI"
        And I should see "Indéterminé" in the ".progress-bar" element
        And I should see "Ce projet ne contient pas encore assez de texte et de faits marquants pour calculer un score précis"
