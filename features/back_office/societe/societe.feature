Feature: Création d'une nouvelle société

    Background:
        Given I have loaded fixtures from "back_office/societe/societe.yml"
        And I am on "/connexion"
        When I fill in the following:
            | _username | user@bo.dev |
            | _password | user        |
        And I press "Connexion"
        And I follow "SociétéTest"

    Scenario: Je peux envoyer un mail d'invitation à un administrateur
        Then I should see "admin@societe.dev"
        And I should find toastr message "Aucun administrateur de la société SociétéTest n'a encore pas reçu de notifications. Envoyez un email d'invitation depuis cette page afin qu'il puisse finaliser son inscription !"

        When I press "Envoyer un email d'invitation"
        Then I should find toastr message "Un email avec un lien d'invitation a été envoyé à l'administrateur \"admin@societe.dev\""
        And I should see "Dernière invitation envoyée le"
        And I should not see "Aucun administrateur de la société SociétéTest n'a encore pas reçu de notifications. Envoyez un email d'invitation depuis cette page afin qu'il puisse finaliser son inscription !"

    Scenario: Je peux ajouter un autre administrateur sur une société déjà créée
        When I fill in the following:
            | user_email[invitationEmail] | autre_admin@societe.dev |
        And I press "Inviter un autre administrateur"
        Then I should find toastr message "L'administrateur a été ajouté ! Vous pouvez lui envoyer un email d'invitation afin qu'il finalise son inscription"
