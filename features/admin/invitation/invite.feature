Feature: L'admin (ou référent) peut inviter des nouvels utilisateurs
    dans sa société en les invitant par leur email.

    Background:
        Given I have loaded fixtures from "admin/invitation/invite.yml"

    Scenario: L'admin peut inviter un utilisateur avec son email et un rôle.
        Given I am on "/connexion"
        And I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"

        When I follow "Inviter un nouvel utilisateur"
        Then I should see "Inviter un nouvel utilisateur"

        When I fill in the following:
            | invite_user[email] | nouveau-cdp@societe.dev |
            | invite_user[role]  | ROLE_FO_CDP             |
        And I press "Inviter"
        Then I should see "Un email avec un lien d'invitation a été envoyé à \"nouveau-cdp@societe.dev\""

    Scenario: L'utilisateur invité peut finaliser son inscription après avoir suivi le lien d'invitation qu'il a recu.
        Given I am on "/inscription/cV2bvNJg4e_zkzXis-rfKlih"
        Then I should see "Finalisation de votre inscription"
        And I should see "vous inscrire sur RDI manager, et de rejoindre la société SociétéTest avec le rôle Chef de projet"
        And I fill in the following:
            | finalize_inscription[prenom]           | JeSuis        |
            | finalize_inscription[nom]              | LeNouveau     |
            | finalize_inscription[password][first]  | m0nM0tdepass3 |
            | finalize_inscription[password][second] | m0nM0tdepass3 |
        And I press "Finir mon inscription"
        Then I should be on "/connexion"

        # Vérifie que je peux me connecter après la finalisation
        When I fill in the following:
            | _username | invite@societe.dev |
            | _password | m0nM0tdepass3      |
        And I press "Connexion"
        Then I should see "SociétéTest | JeSuis"

        # Vérifie que le lien d'invitation ne fonctionne plus une fois la finalisation terminée.
        When I go to "/inscription/cV2bvNJg4e_zkzXis-rfKlih"
        Then the response status code should be 404
