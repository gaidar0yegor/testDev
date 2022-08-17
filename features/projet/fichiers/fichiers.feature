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

        When I follow "Projets"
        And I follow "PTEST"
        And I follow "Liste des fichiers"
        Then I should see "Liste des fichiers" in the "h1" element
        And I should see "fichier_de_contributeur.txt"
        And I should see "fichier_de_cdp.txt"
        But I should not see "Ajouter un fichier"

        When I follow "fichier_de_cdp.txt"
        Then the response status code should not be 403

    Scenario: Un contributeur peut renommer un fichier 
        Given I am on "/connexion"
        And I fill in the following:
            | _username | contributeur@societe.dev  |
            | _password | contributeur              |
        And I press "Connexion"

        When I follow "Projets"
        And I follow "PTEST"
        And I follow "Liste des fichiers"
        When I click on the 1st "[href='/corp/projets/1/fichiers/1/modifier']" element
        Then I should see "Modifier un fichier" in the "h1" element
        And I fill in the following:
            | fichier_projet_modifier[fichier][nomFichier] | business-plan.txt |
        And I press "Modifier"

        And I should find toastr message "Votre fichier fichier_de_contributeur.txt à bien été modifié en business-plan.txt"
        Then I should see "business-plan.txt" in the "form[name='projet_fichier_projets']" element

    Scenario: Un contributeur ne peut pas changer l'extension d'un fichier
        Given I am on "/connexion"
        And I fill in the following:
            | _username | contributeur@societe.dev  |
            | _password | contributeur              |
        And I press "Connexion"

        When I follow "Projets"
        And I follow "PTEST"
        And I follow "Liste des fichiers"
        When I click on the 1st "[href='/corp/projets/1/fichiers/1/modifier']" element
        Then I should see "Modifier un fichier" in the "h1" element
        And I fill in the following:
            | fichier_projet_modifier[fichier][nomFichier] | business-plan.php |
        And I press "Modifier"

        And I should find toastr message "Votre fichier fichier_de_contributeur.txt à bien été modifié en business-plan.txt"
        Then I should see "business-plan.txt" in the "form[name='projet_fichier_projets']" element

    Scenario: Un observateur ne peut pas renommer les fichiers
        Given I am on "/connexion"
        And I fill in the following:
            | _username | observateur@societe.dev  |
            | _password | observateur              |
        And I press "Connexion"

        When I follow "Projets"
        And I follow "PTEST"
        And I follow "Liste des fichiers"
        Then I should see a "[href='/corp/projets/1/fichiers/1/modifier'].disabled" element
        When I go to "/corp/projets/1/fichiers/1/modifier"
        Then the response status code should be 403

    Scenario: Un utilisateur ne peut pas voir et télécharger les fichiers si il n'est pas au moins observateur sur le projet
        Given I am on "/connexion"
        And I fill in the following:
            | _username | pas_acces@societe.dev  |
            | _password | pas_acces              |
        And I press "Connexion"

        When I go to "/corp/projets/1/fichiers"
        Then the response status code should be 403

        When I go to "/corp/projets/1/fichiers/1"
        Then the response status code should be 403

    Scenario: Un contributeur peut téléverser un fichier
        Given I am on "/connexion"
        And I fill in the following:
            | _username | contributeur@societe.dev  |
            | _password | contributeur              |
        And I press "Connexion"

        When I go to "/corp/projets/1"
        When I follow "Ajouter un fichier"
        And I should see "Ajouter un fichier"
