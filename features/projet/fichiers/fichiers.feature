Feature: Les contributeurs d'un projet peuvent téléverser des fichiers
    sur le projet, comme des pièces jointes.

    Background:
        Given I have loaded fixtures from "projet/fichiers/fixtures.yml"

    Scenario: Un observateur du projet peut voir les projets en lecture seule
        Given I am on "/connexion"
        And I fill in the following:
            | _username | observateur@societe.dev  |
            | _password | observateur              |
        And I press "Connexion"

        When I follow "Liste des projets"
        And I follow "PTEST"
        And I follow "Voir tous les fichiers"
        Then I should see "Liste des fichiers" in the "h1" element
        And I should see "fichier_de_contributeur.txt"
        And I should see "fichier_de_cdp.txt"
        But I should not see "Ajouter un fichier"

        When I follow "fichier_de_cdp.txt"
        Then the response status code should not be 403

    Scenario: Un utilisateur ne peut pas voir et télécharger les fichiers si il n'est pas au moins observateur sur le projet
        Given I am on "/connexion"
        And I fill in the following:
            | _username | pas_acces@societe.dev  |
            | _password | pas_acces              |
        And I press "Connexion"

        When I go to "/projets/1/fichiers"
        Then the response status code should be 403

        When I go to "/projets/1/fichiers/1"
        Then the response status code should be 403

    Scenario: Un contributeur peut téléverser un fichier
        Given I am on "/connexion"
        And I fill in the following:
            | _username | contributeur@societe.dev  |
            | _password | contributeur              |
        And I press "Connexion"

        When I go to "/projets/1"
        When I follow "Ajouter un fichier"
        And I should see "Ajouter un fichier"
