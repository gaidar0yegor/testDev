Feature: Création de projet

    Background:
        Given I have loaded fixtures from "projet/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | cdp@societe.dev  |
            | _password | cdp              |
        And I press "Connexion"

    Scenario: Un chef de projet peut créer un projet
        Given I am on "/corp/projets"
        When I follow "Créer un projet"
        Then I should be on "/corp/projets/creation"
        When I fill in the following:
            | projet_form[acronyme] | MPT           |
            | projet_form[titre]    | MonProjetTest |
        And I press "Soumettre"
        Then I should find toastr message "Le projet \"MonProjetTest\" a été créé"
        When I follow "J'ajouterai des contributeurs plus tard"
        Then the url should match "/projets/"
        And I should see "MPT" in the "h1" element

    Scenario: Le chef de projet est invité à ajouter des contributeurs dés la création d'un projet
        Given I am on "/corp/projets"
        When I follow "Créer un projet"
        And I fill in the following:
            | projet_form[acronyme] | MPT           |
            | projet_form[titre]    | MonProjetTest |
        And I press "Soumettre"
        Then I should see "Ajouter des contributeurs" in the "h1" element
        And I should see "Qui contribue à ce projet ?"
        And I should see "User Eureka"
        And I should see "Admin Eureka"

        When I check "User Eureka"
        And I check "Admin Eureka"
        And I press "Ajouter des contributeurs"
        Then I should find toastr message "Les 2 contributeurs ont été ajoutés au projet"
        And I should see "Contributeurs (3)"
