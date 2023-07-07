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

        When I follow "Inviter un utilisateur"
        Then I should see "Inviter un nouvel utilisateur"

        When I fill in the following:
            | invite_user[invitationEmail] | nouveau-cdp@societe.dev |
            | invite_user[role]            | SOCIETE_CDP             |
            | invite_user[societeUserPeriods][0][dateEntry] | 01 janvier 2020 |
        And I press "Inviter"
        Then I should find toastr message "Un lien d'invitation a été envoyé"

    Scenario: L'utilisateur invité peut finaliser son inscription après avoir suivi le lien d'invitation qu'il a recu.
        Given I am on "/corp/inscription/cV2bvNJg4e_zkzXis-rfKlih"
        Then I should see "Finalisation de votre inscription"
        And I should see "Vous êtes sur le point de rejoindre la société SociétéTest avec le rôle Chef de Projet"

        # Créer mon compte
        When I follow "Créer mon compte RDI-Manager"
        And I fill in the following:
            | finalize_inscription[prenom]           | JeSuis        |
            | finalize_inscription[nom]              | LeNouveau     |
            | finalize_inscription[password][first]  | m0nM0tdepass3 |
            | finalize_inscription[password][second] | m0nM0tdepass3 |
        And I press "Créer mon compte"
        Then I should be on "/corp/inscription/rejoindre-la-societe/cV2bvNJg4e_zkzXis-rfKlih"
        And I should see "Vous êtes sur le point de rejoindre la société SociétéTest avec le rôle Chef de Projet"
        And I should see "Vous rejoindrez la société avec votre compte RDI-Manager JeSuis LeNouveau (invite@societe.dev)"

        # Rejoindre la société
        When I press "Rejoindre la société SociétéTest"
        Then I should find toastr message "Vous avez rejoint la société"
        And I should see "Tableau de bord"

        # Vérifie que je peux me connecter après la finalisation
        When I follow "Déconnexion"
        And I fill in the following:
            | _username | invite@societe.dev |
            | _password | m0nM0tdepass3      |
        And I press "Connexion"
        Then I should see "SociétéTest"
        And I should see "JeSuis"

        # Vérifie que le lien d'invitation ne fonctionne plus une fois la finalisation terminée.
        When I go to "/inscription/cV2bvNJg4e_zkzXis-rfKlih"
        Then the response status code should be 404

    Scenario: L'admin peut inviter un user avec seulement un numéro de téléphone
        Given I am on "/connexion"
        And I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"

        When I follow "Inviter un utilisateur"
        Then I should see "Inviter un nouvel utilisateur"

        When I fill in the following:
            | invite_user[invitationTelephone][country] | FR |
            | invite_user[invitationTelephone][number] | 06 05 04 03 02 |
            | invite_user[role]                        | SOCIETE_CDP    |
        And I press "Inviter"
        Then I should find toastr message "Un lien d'invitation a été envoyé"
