Feature: Le chef de projet doit pouvoir inviter un nouvel utilisateur
    sur RDI manager, et cet utilisateur aura déjà un accès sur son projet.

    Scenario: Chef de projet peut mettre à jour les contributeurs sans avoir l'erreur "Pourcentages > 100%"
        Given I have loaded fixtures from "projet/gestion_participants/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | cdp@societe.dev  |
            | _password | cdp              |
        And I press "Connexion"
        And I am on "/projet/1/participants"

        When I follow "Inviter un nouvel utilisateur sur ce projet"
        And I fill in the following:
            | invite_user_sur_projet[email] | invite@societe.dev |
            | invite_user_sur_projet[role]  | CONTRIBUTEUR       |
        And I press "Inviter"
        Then I should see "Un email avec un lien d'invitation a été envoyé à \"invite@societe.dev\""
