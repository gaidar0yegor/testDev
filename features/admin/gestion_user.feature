Feature: Le référent peut voir, modifier et supprimer ses utilisateurs.

    Background:
        Given I have loaded fixtures from "admin/users.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"
        And I follow "Liste des utilisateurs"

    Scenario: Le référent peut voir toutes les infos d'un utilisateur en particulier
        When I click on the 1st "[href='/utilisateurs/2']" element
        Then I should see "Compte de Utilisateur Eureka"

    Scenario: Le référent ne peut pas voir les infos des utilisateurs des autres société
        When I go to "/utilisateurs/3"
        Then the response status code should be 403
        And I should not see "Modification de Utilisateur Eureka"

    Scenario: Le référent peut modifier les infos d'un utilisateur en particulier
        When I click on the 1st "[href='/utilisateurs/2/modifier']" element
        Then I should see "Modification de Utilisateur Eureka"

        When I fill in the following:
            | utilisateurs_form[nom]    | NomModifié    |
            | utilisateurs_form[prenom] | PrénomModifié |
            | utilisateurs_form[role]   | ROLE_FO_CDP   |
        And I press "Mettre à jour"
        Then I should see "Compte de PrénomModifié NomModifié"
        And I should see "Chef de projet" in the ".main-container" element

    Scenario: Le référent ne peut pas modifier les infos des utilisateurs des autres société
        When I go to "/utilisateurs/3/modifier"
        Then the response status code should be 403
        And I should not see "AutreSociete"
