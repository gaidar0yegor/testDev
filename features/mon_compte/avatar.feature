Feature: Pouvoir me mettre un avatar

    Background:
        Given I have loaded fixtures from "mon_compte/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

    Scenario: Je peut changer mon avatar
        Given I am on "/corp/mon-compte"
        And I follow "Modifier mon avatar"
        When I attach the file "mon_compte/avatar.jpg" to "avatar[file]"
        And I press "Mettre à jour"
        Then I should find toastr message "Votre avatar a été mis à jour"
