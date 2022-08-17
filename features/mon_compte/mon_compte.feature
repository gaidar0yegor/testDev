Feature: Mon compte, voir et modifier mes données personnelles.

    Background:
        Given I have loaded fixtures from "mon_compte/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

    Scenario: Je peux voir mes données personnelles
        When I go to "/corp/mes-societes"
        And I follow "Mon compte"
        Then I should see "Mon compte" in the "h1" element
        And I should see "Nom Eureka"
        And I should see "Prénom User"
        And I should see "Email user@societe.dev"

    Scenario: Je peux changer mon mot de passe
        Given I am on "/corp/mon-compte"
        When I follow "Changer mon mot de passe"
        Then I should see "Changer mon mot de passe"

        When I fill in the following:
            | update_password[oldPassword]         | user              |
            | update_password[newPassword][first]  | nouveauMotDePasse |
            | update_password[newPassword][second] | nouveauMotDePasse |
        And I press "Mettre à jour mon mot de passe"
        Then I should find toastr message "Votre mot de passe a été mis à jour"

    Scenario: Je doit connaître l'ancien mot de passe pour le changer
        Given I am on "/corp/mon-compte"
        When I follow "Changer mon mot de passe"
        Then I should see "Changer mon mot de passe"

        When I fill in the following:
            | update_password[oldPassword]         | MAUVAIS           |
            | update_password[newPassword][first]  | nouveauMotDePasse |
            | update_password[newPassword][second] | nouveauMotDePasse |
        And I press "Mettre à jour mon mot de passe"
        Then I should find toastr message " Votre ancien mot de passe saisis n'est pas le bon"

    Scenario: Je peut modifier mes infos personnelles
        Given I am on "/corp/mon-compte"
        When I follow "Mettre à jour"
        Then I should see "Modification de mon compte"
        When I fill in the following:
            | Prénom | NouveauPrenom |
        And I press "Mettre à jour"
        Then I should find toastr message "Vos informations personnelles ont été mises à jour"
        And I should see "NouveauPrenom" in the "nav" element

    Scenario: Je peut ajouter mon numéro de téléphone afin de recevoir les notifications importantes
        Given I am on "/corp/mon-compte/modifier"

        When I fill in the following:
            | mon_compte[telephone][number] | mauvais_numero |
        And I press "Mettre à jour"
        Then I should see "Cette valeur n'est pas un numéro de téléphone valide"

        When I fill in the following:
            | mon_compte[telephone][number] | 0102030405 |
        And I press "Mettre à jour"
        Then I should see "Cette valeur n'est pas un numéro de téléphone mobile valide"

        When I fill in the following:
            | mon_compte[telephone][number] | 06457 |
        And I press "Mettre à jour"
        Then I should see "Cette valeur n'est pas un numéro de téléphone mobile valide"

        When I fill in the following:
            | mon_compte[telephone][number] | 0606060606 |
        And I press "Mettre à jour"
        Then I should find toastr message "Vos informations personnelles ont été mises à jour"
