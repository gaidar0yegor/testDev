Feature: Recommandation

    Scenario: Un utilisateur anonyme peut recommander RDI-Manager à ses propres contacts
        Given I am on "/connexion"

        When I click on the 1st "footer [href='/recommander-rdi-manager']" element
        Then I should see "Recommander RDI-Manager"

        When I fill in the following:
            | recommandation_message[to] | mon-contact@test.fr |
        And I press "Envoyer"
        Then I should find toastr message "Un email de recommandation a bien été envoyé à \"mon-contact@test.fr\""

    Scenario: Un utilisateur connecté peut recommander RDI-Manager à ses propres contacts
        Given I have loaded fixtures from "recommandation/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev |
            | _password | user             |
        And I press "Connexion"

        When I click on the 1st "footer [href='/recommander-rdi-manager']" element
        Then I should see "Recommander RDI-Manager"
        And the "recommandation_message[from]" field should contain "user@societe.dev"

        When I fill in the following:
            | recommandation_message[to] | mon-contact@test.fr |
        And I press "Envoyer"
        Then I should find toastr message "Un email de recommandation a bien été envoyé à \"mon-contact@test.fr\""

    Scenario: Un utilisateur connecté avec un n° de téléphone peut recommander RDI-Manager à ses propres contacts
        Given I have loaded fixtures from "recommandation/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | 06 05 04 03 02 |
            | _password | user           |
        And I press "Connexion"

        When I click on the 1st "footer [href='/recommander-rdi-manager']" element
        Then I should see "Recommander RDI-Manager"

        When I fill in the following:
            | recommandation_message[to] | mon-contact@test.fr |
        And I press "Envoyer"
        Then I should find toastr message "Un email de recommandation a bien été envoyé à \"mon-contact@test.fr\""
