Feature: Mot de passe oublié

    Scenario: Je dois pouvoir m'envoyer un email de réinitialisation de mot de passe
        Given I have loaded fixtures from "connexion/fixtures.yml"
        When I am on "/connexion"
        And I follow "Mot de passe oublié"
        Then I should see "Réinitialisation de mot de passe"

        When I fill in the following:
            | form[username] | user@societe.dev |
        And I press "Demander un lien de réinitialisation"
        Then I should find toastr message "Un lien de réinitialisation de mot de passe vous a été envoyé"

    Scenario: Je dois pouvoir m'envoyer un SMS de réinitialisation de mot de passe
        Given I have loaded fixtures from "connexion/fixtures.yml"
        When I am on "/connexion"
        And I follow "Mot de passe oublié"
        And I fill in the following:
            | form[username] | +33605040302 |
        And I press "Demander un lien de réinitialisation"
        Then I should find toastr message "Un lien de réinitialisation de mot de passe vous a été envoyé"

    Scenario: Je dois pouvoir changer mon mot de passe après avoir suivi mon lien de réinitialisation
        Given I have loaded fixtures from "connexion/fixtures.yml"
        And I am on "/reinitialiser-mot-de-passe/tokenSecr3t"
        Then I should see "Réinitialisation de mot de passe"

        When I fill in the following:
            | form[password][first]  | nouveauMotDePasse |
            | form[password][second] | nouveauMotDePasse |
        And I press "Valider mon mot de passe"
        Then I should find toastr message "Votre mot de passe a été changé"

        When I go to "/connexion"
        And I fill in the following:
            | _username | oubli@societe.dev |
            | _password | nouveauMotDePasse |
        And I press "Connexion"
        Then I should see "Oubli"
        And I should see "Société"

    Scenario: Je ne doit pas pouvoir utiliser un lien de réinitialisation généré il y a trop longtemps
        Given I have loaded fixtures from "connexion/fixtures.yml"
        When I go to "/reinitialiser-mot-de-passe/tokenSecr3tExpired"
        Then the response status code should be 404
        And I should see "Ce lien de réinitialisation est expiré ou invalide"
