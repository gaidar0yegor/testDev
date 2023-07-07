Feature: Multi-société, current_societe_user :
    lorsque je me connecte à une société, et que je me reconnecte plus tard,
    je reviens automatiquement sur la même société.

    Background:
        Given I have loaded fixtures from "multi_societe/current_societe.yml"

#    Activer Cahier de labo
#    Scenario: Je dois toujours revenir sur la page de switch quand j'ai plusieurs sociétés
#        Given I am on "/connexion"
#        And I fill in the following:
#            | _username | user@societe.dev  |
#            | _password | user              |
#        And I press "Connexion"
#
#        Then I should see "Bienvenue sur le portail de RDI Manager" in the "h1" element

    Scenario: Je dois toujours revenir sur la page de switch quand j'ai plusieurs sociétés
        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

        Then I should see "Mes sociétés" in the "h1" element

    Scenario: Je suis déconnecté de la société si mon accès a été désactivé par l'admin
        Given I am on "/connexion"
        And I fill in the following:
            | _username | user_disabled@societe.dev  |
            | _password | user_disabled              |
        And I press "Connexion"

        Then I should not see "Société1 | UserDisabled" in the "nav" element
