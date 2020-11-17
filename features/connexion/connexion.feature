Feature: Page de connexion

    Scenario: Je dois pouvoir accéder à la page de connexion si je ne suis pas connécté
        Given I am on "/connexion"
        Then the response status code should be 200
        And I should see "Email / Identifiant"

    Scenario: Je peux me connecter
        Given I have loaded fixtures from "connexion/fixtures.yml"
        And I am on "/connexion"
        When I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"
        And I should see "Bienvenue"
        And I should see "User Eureka"

    Scenario: Je reste sur la page de connexion si je me trompe de mot de passe
        Given I have loaded fixtures from "connexion/fixtures.yml"
        And I am on "/connexion"
        When I fill in the following:
            | _username | user@societe.dev     |
            | _password | MAUVAIS_MOT_DE_PASSE |
        And I press "Connexion"
        Then I should be on "/connexion"
        And I should not see "Bienvenue"
        And I should not see "User Eureka"

    Scenario: Je ne peux pas me connecter si mon compte a été désactivé par l'administrateur
        Given I have loaded fixtures from "connexion/fixtures.yml"
        And I am on "/connexion"
        When I fill in the following:
            | _username | user-desactive@societe.dev     |
            | _password | user-desactive |
        And I press "Connexion"
        Then I should be on "/connexion"
        And I should not see "Bienvenue"
        And I should see "Le compte est désactivé"
