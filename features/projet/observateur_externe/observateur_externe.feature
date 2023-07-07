Feature: Affichage de la liste des projets de l'utilisateur

    Background:
        Given I have loaded fixtures from "projet/observateur_externe/fixtures.yml"

    Scenario: Le chef de projet peut inviter un observateur externe
        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe1.dev |
            | _password | user              |
        And I press "Connexion"

        When I go to "/corp/projets"
        And I follow "PTEST"
        And I follow "Gérer les participants"
        Then I should see "Inviter un observateur externe"

        When I follow "Inviter un observateur externe"
        And I fill in the following:
            | invite_observateur_externe[invitationEmail] | observateur_externe@externe.dev |
        And I press "Inviter un observateur externe"
        Then I should find toastr message "Une notification avec un lien d'invitation a été envoyée à votre observateur externe"
        And I should see "En cours d'invitation..." in the "observateur_externe@externe.dev" row

    Scenario: L'observateur externe peut rejoindre le projet avec son lien d'invitation
        When I go to "/corp/invitation-observateur-externe/INVITATION_TOKEN"
        Then I should see "Vous êtes sur le point de rejoindre le projet PTEST"

        When I follow "J'ai déjà un compte RDI-Manager"
        And I fill in the following:
            | _username | user@societe2.dev |
            | _password | user              |
        And I press "Connexion"

        And I press "Rejoindre le projet PTEST en tant qu'observateur externe"
        Then I should see "Vous avez rejoint le projet"
        And I should see "Projet Société1 / PTEST"
        And I should see "Chef de projet : User Societe1"

        When I go to "/corp/mes-societes"
        And I follow "Tous mes projets"
        Then I should see "Mes projets externes" in the "h1" element
        When I follow "PTEST"
        Then I should see "Projet Société1 / PTEST" in the "h1" element

    Scenario: Le chef de projet peut retirer un observateur externe
        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe1.dev |
            | _password | user              |
        And I press "Connexion"

        When I go to "/corp/projets"
        And I follow "PTEST"
        And I follow "Gérer les participants"
        Then I should see "En cours d'invitation..." in the "email@unused.dev" row

        When I press "Retirer cet observateur externe"
        Then I should find toastr message "Cet observateur externe a été retiré"
        Then I should not see "En cours d'invitation..."
