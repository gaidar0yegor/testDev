Feature: Gestion des faits marquants d'un projet

    Background:
        Given I have loaded fixtures from "projet/fait_marquant/fixtures.yml"

    Scenario: Un contributeur peut créer un fait marquant
        Given I am on "/connexion"
        And I fill in the following:
            | _username | contributeur@societe.dev  |
            | _password | contributeur              |
        And I press "Connexion"
        And I am on "/corp/projets/1"

        When I follow "Ajouter un fait marquant dans un nouvel onglet"
        Then I should see "Ajouter un fait marquant" in the "h1" element
        And I should see "Projet PTEST" in the "h2" element

        When I fill in the following:
            | fait_marquant[titre]       | Mon fait marquant          |
            | fait_marquant[description] | J'ai créé un fait marquant |
        And I press "Publier"
        Then I should find toastr message "Le fait marquant \"Mon fait marquant\" a été ajouté au projet"
        And I should see "J'ai créé un fait marquant" in the ".timeline" element

    Scenario: Un observateur ne peut pas créer de fait marquant
        Given I am on "/connexion"
        And I fill in the following:
            | _username | observateur@societe.dev  |
            | _password | observateur              |
        And I press "Connexion"
        And I am on "/corp/projets/1"
        Then I should see "Seuls les contributeurs peuvent ajouter un fait marquant" in the ".timeline" element

        When I go to "/corp/projets/1/fait-marquants/ajouter"
        Then the response status code should be 403
        And I should not see "Ajouter un fait marquant"

    Scenario: Un contributeur peut modifier son fait marquant
        Given I am on "/connexion"
        And I fill in the following:
            | _username | contributeur@societe.dev  |
            | _password | contributeur              |
        And I press "Connexion"
        And I am on "/corp/projets/1"

        When I click on the 1st ".timeline a.edit-fait-marquant" element
        Then I should see "Modifiez votre fait marquant" in the "h1" element

        When I fill in the following:
            | fait_marquant[titre]       | Fait marquant déjà créé et modifié            |
            | fait_marquant[description] | J'ai créé un fait marquant et je l'ai modifié |
        And I press "Publier"
        Then I should find toastr message "Le fait marquant \"Fait marquant déjà créé et modifié\" a été modifié"
        And I should see "J'ai créé un fait marquant et je l'ai modifié" in the ".timeline" element

    Scenario: Un contributeur peut supprimer son fait marquant
        Given I am on "/connexion"
        And I fill in the following:
            | _username | contributeur@societe.dev  |
            | _password | contributeur              |
        And I press "Connexion"
        And I am on "/corp/projets/1"

        Then I should see "Contenu du fait marquant déjà créé"

        When I click on the 1st ".timeline a.edit-fait-marquant" element
        And I press "Supprimer"
        Then I should find toastr message "Le fait marquant \"Fait marquant déjà créé\" a été supprimé"
        But I should not see "Contenu du fait marquant déjà créé"

    Scenario: Un contributeur ne peut pas modifier les faits marquants des autres contributeurs
        Given I am on "/connexion"
        And I fill in the following:
            | _username | contributeur2@societe.dev  |
            | _password | contributeur2              |
        And I press "Connexion"
        And I am on "/corp/projets/1"

        When I click on the 1st ".timeline a.edit-fait-marquant" element
        Then I should not see "Modifiez votre fait marquant" in the "h1" element

    Scenario: Un chef de projet peut modifier les faits marquants des autres contributeurs
        Given I am on "/connexion"
        And I fill in the following:
            | _username | cdp@societe.dev  |
            | _password | cdp              |
        And I press "Connexion"
        And I am on "/corp/projets/1"

        When I click on the 1st ".timeline a.edit-fait-marquant" element
        Then I should see "Modifiez votre fait marquant" in the "h1" element

     Scenario: Un collaborateur peut voir les contributeur du projet
        Given I am on "/connexion"
        And I fill in the following:
            | _username | cdp@societe.dev  |
            | _password | cdp              |
        And I press "Connexion"
        And I am on "/corp/projets/1"

        Then I should see "Contributeurs (3)"
        And I should see an "img[alt='Avatar de Contributeur Eureka']" element
        And I should see an "img[alt='Avatar de Contributeur2 Eureka']" element
        And I should see "Observateur Eureka"
