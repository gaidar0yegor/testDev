Feature: Page de connexion

    Scenario: Je dois pouvoir accéder à la page de connexion si je ne suis pas connécté
        Given I am on "/connexion"
        Then the response status code should be 200
        And I should see "Connexion"
        And I should see "Identifiant"
