Feature: L'onboarding doit aider les nouveaux utilisateurs
    à prendre en main RDI-Manager en les guidant pour rentrer
    leurs premiers projets et utilisateurs, et utiliser les fonctionnalités principales.

    Background:
        Given I have loaded fixtures from "onboarding/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"

    Scenario: Je dois voir les étapes de prise en main
        Then I should see "Prise en main de RDI-Manager" in the ".onboarding-messages" element
        And I should see "Suivi de temps" in the ".onboarding-messages" element

    Scenario: Je peux suivre et valider l'étape de création de mon premier projet
        Then I should not see a ".onboarding-messages .nav-link.done:contains('Ajoutez vos projets')" element

        When I click on the 1st ".onboarding-messages .nav-link:not(.done) a:contains('Ajoutez vos projets')" element
        Then I should be on "/corp/projets/creation"

        When I fill in the following:
            | projet_form[acronyme] | MPT           |
            | projet_form[titre]    | MonProjetTest |
        And I press "Soumettre"
        Then I should see a ".onboarding-messages .nav-link.done:contains('Ajoutez vos projets')" element

    Scenario: Je peux ignorer et fermer l'onboarding
        Then I should see an ".onboarding-messages" element
        And I should see "Ignorer et ne pas faire ces étapes" in the ".onboarding-messages a" element

        When I send a POST request to "/corp/api/onboarding/close"
        Then the response status code should be 204

        When I go to "/"
        Then I should not see an ".onboarding-messages" element
