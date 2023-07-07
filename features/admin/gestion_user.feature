Feature: Le référent peut voir, modifier et supprimer ses utilisateurs.

    Background:
        Given I have loaded fixtures from "admin/users.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"
        And I follow "Utilisateurs"

    Scenario: Le référent peut voir toutes les infos d'un utilisateur en particulier
        When I click on the 1st "[href='/corp/utilisateur/2']" element
        Then I should see "Utilisateur Eureka" in the "h1" element

    Scenario: Le référent ne peut pas voir les infos des utilisateurs des autres société
        When I go to "/corp/utilisateur/3"
        Then the response status code should be 403
        And I should not see "Modification de Utilisateur Eureka"

    Scenario: Le référent peut modifier les infos d'un utilisateur en particulier
        When I click on the 1st "[href='/corp/utilisateur/2/modifier']" element
        Then I should see "Modification de Utilisateur Eureka"

        When I fill in the following:
            | societe_user[role] | SOCIETE_CDP |
        And I press "Mettre à jour"
        Then I should see "Utilisateur Eureka" in the "h1" element
        And I should see "Chef de Projet" in the ".main-container" element

    Scenario: Le référent ne peut pas modifier les infos des utilisateurs des autres société
        When I go to "/corp/utilisateur/3/modifier"
        Then the response status code should be 403
        And I should not see "AutreSociete"

    Scenario: Le référent peut désactiver et réactiver un utilisateur
        When I click on the 1st "[href='/corp/utilisateur/2']" element
        Then I should see "Actif"

        When I follow "Mettre à jour"
        And I press "Désactiver"
        Then I should find toastr message "veuillez préciser sa date de sortie"
        And I fill in the following:
            | societe_user[societeUserPeriods][0][dateLeave] | 20 janvier 2050 |
        And I press "Mettre à jour"
        Then I should find toastr message "Les informations de l'utilisateur ont été modifiées"

        And I am on "/corp/utilisateur/2/modifier"
        And I press "Désactiver"
        Then I should find toastr message "Utilisateur Eureka a été désactivé, il ne pourra plus se connecter"
        When I follow "Utilisateurs"
        And I click on the 1st "[href='/corp/utilisateur/2']" element
        And I should see "Désactivé"

        When I follow "Mettre à jour"
        And I press "Ré-activer"
        Then I should find toastr message "veuillez préciser sa date d'entrèe"
        And I fill in the following:
            | societe_user[societeUserPeriods][1][dateEntry] | 20 janvier 2051 |
        And I press "Mettre à jour"
        Then I should find toastr message "Les informations de l'utilisateur ont été modifiées"

        And I am on "/corp/utilisateur/2/modifier"
        And I press "Ré-activer"
        Then I should find toastr message "Utilisateur Eureka a été activé, il pourra se connecter de nouveau"
        When I follow "Utilisateurs"
        And I click on the 1st "[href='/corp/utilisateur/2']" element
        And I should see "Actif"

    Scenario: Le référent ne peut pas se désactiver lui-même car il serait bloqué
        When I click on the 1st "[href='/corp/utilisateur/1']" element
        And I follow "Mettre à jour"
        And I press "Désactiver"
        Then I should not see "Admin Eureka a été désactivé, il ne pourra plus se connecter"

    Scenario: Le référent ne peut pas désactiver les utilisateurs des autres société
        When I send a POST request to "/corp/utilisateur/3/desactiver"
        Then the response status code should be 403

    Scenario: L'admin peut voir les projets dont l'utilisateur participe
        When I click on the 1st "[href='/corp/utilisateur/2']" element
        Then I should see "Ses projets" in the "h2" element
        And I should see "Contributeur sur le projet PT"

    Scenario: L'admin peut voir le graphique des temps passés de l'utilisateurs sur ses projets
        When I click on the 1st "[href='/corp/utilisateur/2']" element
        Then I should see "Temps passé en" in the "h2" element containing "Temps passé en"

    Scenario: L'admin peut définir une date d'entrée d'un user
        When I click on the 1st "[href='/corp/utilisateur/2/modifier']" element
        And I fill in the following:
            | societe_user[societeUserPeriods][0][dateEntry] | 01 janvier 2021 |
        And I press "Mettre à jour"
        Then I should find toastr message "Les informations de l'utilisateur ont été modifiées"
        And I should see "Date d'entrée dans la société 1 janv. 2021"

        When I follow "Activité"
        Then I should see "Utilisateur Eureka a rejoint la société. le 1 janv. 2021"

    Scenario: L'admin peut définir une date de sortie d'un user
        When I click on the 1st "[href='/corp/utilisateur/2/modifier']" element
        And I fill in the following:
            | societe_user[societeUserPeriods][0][dateLeave] | 20 janvier 2021 |
        And I press "Mettre à jour"
        Then I should find toastr message "Les informations de l'utilisateur ont été modifiées"
        And I should see "Date de sortie de la société 20 janv. 2021"

        When I follow "Activité"
        Then I should see "Utilisateur Eureka a quitté la société. le 20 janv. 2021"

    Scenario: Je ne vois pas l'activité future
        When I click on the 1st "[href='/corp/utilisateur/2/modifier']" element
        And I fill in the following:
            | societe_user[societeUserPeriods][0][dateEntry] | 1 janvier 2020 |
            | societe_user[societeUserPeriods][0][dateLeave] | 20 janvier 2050 |
        And I press "Mettre à jour"
        When I follow "Activité"
        Then I should see "Utilisateur Eureka a rejoint la société. le 1 janv. 2020"
        But I should not see "Utilisateur Eureka a quitté la société. le 20 janvier 2050"

    Scenario: L'activité 'a rejoint la société' est bien remplacée (et non dupliquée) lorsque je modifie la date d'entrée
        When I click on the 1st "[href='/corp/utilisateur/2/modifier']" element
        And I fill in the following:
            | societe_user[societeUserPeriods][0][dateLeave] | 1 janvier 2021 |
        And I press "Mettre à jour"
        And I follow "Mettre à jour"
        And I fill in the following:
            | societe_user[societeUserPeriods][0][dateLeave] | 2 janvier 2021 |
        And I press "Mettre à jour"
        And I follow "Activité"
        Then I should see "Utilisateur Eureka a quitté la société. le 2 janv. 2021"
        And I should not see "Utilisateur Eureka a quitté la société. le 1 janv. 2021"
