Feature: Seul l'admin à accès à la gestion des rôles d'un user sur tous les projets

    Scenario: Un chef de projet n'a pas accès à la gestion des rôles d'un user
        Given I have loaded fixtures from "admin/acces_projets/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | cdp@societe.dev  |
            | _password | cdp              |
        And I press "Connexion"
        And I go to "[href='/corp/utilisateur/3']"
        Then I should not see "Gérer ses accès aux projets"

        When I go to "/corp/mon_equipe/utilisateurs/3/roles-projets"
        Then the response status code should be 403
        And I should not see "Rôles sur projets de Utilisateur Eureka"
