Feature: Un historique d'activité est conservé afin de suivre les changements
    sur un projet ou l'activité d'un utilisateur.

    Background:
        Given I have loaded fixtures from "activite/fixtures.yml"

    Scenario: Je peux voir qui a créé le projet et quand
        Given I am on "/connexion"
        And I fill in the following:
            | _username | cdp@societe.dev  |
            | _password | cdp              |
        And I press "Connexion"

        When I follow "Créer un projet"
        And I fill in the following:
            | projet_form[acronyme] | MPT           |
            | projet_form[titre]    | MonProjetTest |
        And I press "Soumettre"
        And I follow "Activité"
        Then I should see "Chef de projet Eureka a créé le projet MPT"

        When I follow "Mon compte"
        And I follow "Mon activité"
        Then I should see "Chef de projet Eureka a créé le projet MPT"

    Scenario: Je peux suivre l'ajout des faits marquants dans l'activité
        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

        When I go to "/projets/1"
        And I follow "Fait marquant"
        And I fill in the following:
            | fait_marquant[titre]       | Mon fait marquant          |
            | fait_marquant[description] | J'ai créé un fait marquant |
        And I press "Sauvegarder"
        And I follow "Activité"
        Then I should see "User Eureka a ajouté le fait marquant Mon fait marquant sur le projet PTEST"

        When I follow "Mon compte"
        And I follow "Mon activité"
        Then I should see "User Eureka a ajouté le fait marquant Mon fait marquant sur le projet PTEST"

    Scenario: L'utilisateur ne peut pas voir l'activité d'un autre utilisateur
        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

        When I go to "/admin/utilisateurs/2/activite"
        Then the response status code should be 403
        And I should not see "Activité de Admin Eureka" in the "h1" element
