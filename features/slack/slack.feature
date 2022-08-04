Feature: L'admin peut connecter sa société à son espace Slack

    Background:
        Given I have loaded fixtures from "slack/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"

    Scenario: L'admin voit le bouton "Add to Slack"
        When I follow "Paramètres notifications"
        Then I should see "Intégration Slack"
        And I should see an "a[href^='https://slack.com/oauth/v2/authorize']" element

    Scenario: L'admin voit les chaînes Slack déjà connectées
        When I follow "Paramètres notifications"
        Then I should see "Intégration Slack"
        And I should see "Mon-Espace-Slack / #general"

    Scenario: L'admin peut supprimer une connexion à une chaîne Slack pour ne plus y envoyer de notifications
        When I send a POST request to "/corp/api/slack/remove-token/1"
        Then the response status code should be 204

        When I go to the homepage
        And I follow "Paramètres notifications"
        Then I should not see "Mon-Espace-Slack / #general"
