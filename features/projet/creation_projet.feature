Feature: Création de projet

    Background:
        Given I have loaded fixtures from "projet/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | cdp@societe.dev  |
            | _password | cdp              |
        And I press "Connexion"

    Scenario: Un chef de projet peut créer un projet
        Given I am on "/projets"
        When I follow "Nouveau projet"
        Then I should be on "/infos_projet"
        When I fill in the following:
            | projet_form[titre]    | MonProjetTest |
            | projet_form[acronyme] | MPT           |
        And I press "Soumettre"
        Then the url should match "/fiche/projet/"
        And I should see "MonProjetTest" in the "h1" element
        And I should see "Le projet \"MonProjetTest\" a été créé"
