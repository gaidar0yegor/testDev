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

    Scenario: Je peux voir les modifications sur mes propres faits marquants d'un projet
        Given I am on "/connexion"
        And I fill in the following:
            | _username | cdp@societe.dev  |
            | _password | cdp              |
        And I press "Connexion"

        When I go to "/fait-marquants/2/modifier"
        And the "fait_marquant[titre]" field should contain "FM_cdp"
        And I fill in the following:
            | fait_marquant[titre]       | FM_cdp |
            | fait_marquant[description] | Edit   |
        And I press "Sauvegarder"
        And I follow "Activité"
        Then I should see "Chef de projet Eureka a modifié son fait marquant FM_cdp sur le projet PTEST"

        When I follow "Mon compte"
        And I follow "Mon activité"
        Then I should see "Chef de projet Eureka a modifié son fait marquant FM_cdp sur le projet PTEST"

    Scenario: Je peux voir les modifications sur les faits marquants d'un auteur différent
        Given I am on "/connexion"
        And I fill in the following:
            | _username | cdp@societe.dev  |
            | _password | cdp              |
        And I press "Connexion"

        When I go to "/fait-marquants/1/modifier"
        And the "fait_marquant[titre]" field should contain "FM_user"
        And I fill in the following:
            | fait_marquant[titre]       | FM_user |
            | fait_marquant[description] | Edit    |
        And I press "Sauvegarder"
        And I follow "Activité"
        Then I should see "Chef de projet Eureka a modifié le fait marquant FM_user créé par User Eureka sur le projet PTEST"

        When I follow "Mon compte"
        And I follow "Mon activité"
        Then I should see "Chef de projet Eureka a modifié le fait marquant FM_user créé par User Eureka sur le projet PTEST"

    Scenario: L'utilisateur ne peut pas voir l'activité d'un autre utilisateur
        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

        When I go to "/admin/utilisateurs/2/activite"
        Then the response status code should be 403
        And I should not see "Activité de Admin Eureka" in the "h1" element

    Scenario: L'administrateur désactive un utilisateur
        Given I am on "/connexion"
        And I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"
        And I am on "/utilisateur/2"
        And I press "Désactiver"

        When I go to "/admin/utilisateurs/2/activite"
        Then I should see "Admin Eureka a desactivé votre compte"

    Scenario: L'administrateur réactive un utilisateur
        Given I am on "/connexion"
        And I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"
        And I am on "/utilisateur/2"
        And I press "Désactiver"
        And I press "Ré-activer"

        When I go to "/admin/utilisateurs/2/activite"
        Then I should see "Admin Eureka a ré-activé votre compte"