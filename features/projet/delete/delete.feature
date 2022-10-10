Feature: Suppression d'un projet

    Background:
        Given I have loaded fixtures from "projet/delete/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | cdp@societe.dev  |
            | _password | cdp              |
        And I press "Connexion"

    Scenario: Le chef de projet peut supprimer un projet de test, la vérification lui indique que rien ne sera supprimé.
        When I go to "/corp/projets"
        And I follow "PEMPTY"
        And I follow "Paramétrage du projet"
        And I follow "Vérifier et supprimer le projet"
        Then I should see "Supprimer le projet PEMPTY"
        And I should see 6 ".alert-success" elements
        And I should see 1 ".alert-warning" elements
        And I should see "Aucun temps passé n'a été saisi sur ce projet"
        And I should see "Le projet n'a pas de fait marquant"
        And I should see "Le projet n'a pas de fichiers rattachés"
        And I should see "Le projet n'a pas d'observateur externe"
        And I should see "Le projet a un participant, il sera détaché du projet : Chef de projet Eureka"

        When I press "Supprimer"
        Then I should find toastr message "Le projet PEMPTY a été supprimé"

    Scenario: Le chef de projet peut supprimer un projet bien rempli, la vérification lui indique toutes les données liées qui seront supprimées.
        When I go to "/corp/projets"
        And I follow "PFULL"
        And I follow "Paramétrage du projet"
        And I follow "Vérifier et supprimer le projet"
        Then I should see "Supprimer le projet PFULL"
        And I should see 3 ".alert-danger" elements
        And I should see 2 ".alert-warning" elements
        And I should see "Les contributeurs ont saisis du temps passé sur ce projet dans 2 feuilles de temps. Ces temps passés seront supprimés, et il y aura une différence avec les potentielles feuilles de temps que vous avez déjà imprimé : User Eureka - 80% - novembre 2020 Chef de projet Eureka - 40% - novembre 2020"
      And I should see "Le projet a un fait marquant, il sera supprimé également : Fait marquant déjà créé"
        And I should see "Le projet a un fichier rattaché, il sera supprimé également : fichier_de_contributeur.txt"
        And I should see "Le projet a un observateur externe, il ne pourra plus consulter le projet : Observateur Externe"
        And I should see "Le projet a 2 participants, ils seront détachés du projet : Chef de projet Eureka User Eureka"

        When I press "Supprimer"
        Then I should find toastr message "Le projet PFULL a été supprimé"
