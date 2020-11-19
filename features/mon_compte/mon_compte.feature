Feature: Mon compte, voir et modifier mes données personnelles.

    Background:
        Given I have loaded fixtures from "mon_compte/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

    Scenario: Je peux voir mes données personnelles
        When I follow "Mon compte"
        Then I should see "Mon compte" in the "h1" element
        And I should see "Nom : Eureka"
        And I should see "Prénom : User"
        And I should see "Rôle : Utilisateur"
        And I should see "E-mail : user@societe.dev"

    Scenario: Je peux changer mon mot de passe
        Given I am on "/mon-compte"
        When I follow "Modifier votre mot de passe"
        Then I should see "Changement du mot de passe"

        When I fill in the following:
            | update_password[oldPassword]         | user              |
            | update_password[newPassword][first]  | nouveauMotDePasse |
            | update_password[newPassword][second] | nouveauMotDePasse |
        And I press "Mettre à jour mon mot de passe"
        Then I should see "Votre mot de passe a été mis à jour"

    Scenario: Je doit connaître l'ancien mot de passe pour le changer
        Given I am on "/mon-compte"
        When I follow "Modifier votre mot de passe"
        Then I should see "Changement du mot de passe"

        When I fill in the following:
            | update_password[oldPassword]         | MAUVAIS           |
            | update_password[newPassword][first]  | nouveauMotDePasse |
            | update_password[newPassword][second] | nouveauMotDePasse |
        And I press "Mettre à jour mon mot de passe"
        Then I should see " Votre ancien mot de passe saisis n'est pas le bon"
