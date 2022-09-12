Feature: Un utilisateur peut voir les dernières activité liées à lui même (UserNotification/Activity).

    Background:
        Given I have loaded fixtures from "activite/fixtures.yml"

    Scenario: Je suis notifié par la cloche que quelqu'un a créé un fait marquant sur un de mes projets
        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"
        And I go to "/corp/projets/1"
        And I follow "Ajouter un fait marquant dans un nouvel onglet"
        And I fill in the following:
            | fait_marquant[titre]       | Mon fait marquant          |
            | fait_marquant[description] | J'ai créé un fait marquant |
        And I press "Publier"
        And I follow "Déconnexion"
        And I go to "/connexion"
        And I fill in the following:
            | _username | cdp@societe.dev  |
            | _password | cdp              |
        And I press "Connexion"

        # Retrieve notifications
        When I go to "/corp/api/user-notifications/2"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "notifications" should have 1 element
        And the JSON nodes should be equal to:
            | notifications[0].activity.type                    | fait_marquant_created |
            | notifications[0].activity.parameters.projet       | 1 |
            | notifications[0].activity.parameters.createdBy    | 1 |
            | notifications[0].activity.parameters.faitMarquant | 3 |
        And the JSON node "notifications[0].activity.rendered" should contain "a ajouté le fait marquant"
        And the JSON node "notifications[0].acknowledged" should be false

        # acknowledge notifications
        When I send a POST request to "/corp/api/user-notifications/2" with body:
            """
                {
                    "acknowledgeIds": [1]
                }
            """
        Then the response status code should be 204

        # Retrieve acknowledged notifications
        When I go to "/corp/api/user-notifications/2"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "notifications" should have 1 element
        And the JSON node "notifications[0].acknowledged" should be true

    Scenario: Je ne pas voir les notifications des autres utilisateurs
        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"
        And I go to "/corp/projets/1"
        And I follow "Ajouter un fait marquant dans un nouvel onglet"
        And I fill in the following:
            | fait_marquant[titre]       | Mon fait marquant          |
            | fait_marquant[description] | J'ai créé un fait marquant |
        And I press "Publier"
        When I go to "/corp/api/user-notifications/2"
        Then the response status code should be 403

    Scenario: Je suis notifié quand quelqu'un a modifié mon fait marquant
        Given I am on "/connexion"
        And I fill in the following:
            | _username | cdp@societe.dev  |
            | _password | cdp              |
        And I press "Connexion"
        And I go to "/corp/fait-marquants/1/modifier"
        And I fill in the following:
            | fait_marquant[titre] | FM_Modifié |
        And I press "Publier"
        And I follow "Déconnexion"
        And I go to "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"
        When I go to "/corp/api/user-notifications/1"
        And the JSON nodes should be equal to:
            | notifications[0].activity.type                    | fait_marquant_modified |
            | notifications[0].activity.parameters.projet       | 1 |
            | notifications[0].activity.parameters.createdBy    | 1 |
            | notifications[0].activity.parameters.modifiedBy   | 2 |
            | notifications[0].activity.parameters.faitMarquant | 1 |
        And the JSON node "notifications[0].activity.rendered" should contain "a modifié le fait marquant"
        And the JSON node "notifications[0].acknowledged" should be false
