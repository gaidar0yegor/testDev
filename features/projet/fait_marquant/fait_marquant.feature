Feature: Gestion des faits marquants d'un projet

    Background:
        Given I have loaded fixtures from "projet/fait_marquant/fixtures.yml"

    Scenario: Un contributeur peut créer un fait marquant
        Given I am on "/connexion"
        And I fill in the following:
            | _username | contributeur@societe.dev  |
            | _password | contributeur              |
        And I press "Connexion"
        And I am on "/fiche/projet/1"

        When I follow "Fait marquant"
        Then I should see "Ajouter un fait marquant" in the "h1" element
        And I should see "Projet PTEST" in the "h2" element

        When I fill in the following:
            | fait_marquant[titre]       | Mon fait marquant          |
            | fait_marquant[description] | J'ai créé un fait marquant |
        And I press "Sauvegarder"
        Then I should see "Le fait marquant \"Mon fait marquant\" a été ajouté au projet"
        And I should see "J'ai créé un fait marquant" in the ".timeline" element

    Scenario: Un observateur ne peut pas créer de fait marquant
        Given I am on "/connexion"
        And I fill in the following:
            | _username | observateur@societe.dev  |
            | _password | observateur              |
        And I press "Connexion"
        And I am on "/fiche/projet/1"

        When I follow "Fait marquant"
        Then I should not see "Ajouter un fait marquant" in the "h1" element

    Scenario: Un contributeur peut modifier son fait marquant
        Given I am on "/connexion"
        And I fill in the following:
            | _username | contributeur@societe.dev  |
            | _password | contributeur              |
        And I press "Connexion"
        And I am on "/fiche/projet/1"

        When I click on the 1st ".timeline a:contains('Modifier')" element
        Then I should see "Modifiez votre fait marquant" in the "h1" element

        When I fill in the following:
            | fait_marquant[titre]       | Fait marquant déjà créé et modifié            |
            | fait_marquant[description] | J'ai créé un fait marquant et je l'ai modifié |
        And I press "Sauvegarder"
        Then I should see "Le fait marquant \"Fait marquant déjà créé et modifié\" a été modifié"
        And I should see "J'ai créé un fait marquant et je l'ai modifié" in the ".timeline" element

    Scenario: Un contributeur peut supprimer son fait marquant
        Given I am on "/connexion"
        And I fill in the following:
            | _username | contributeur@societe.dev  |
            | _password | contributeur              |
        And I press "Connexion"
        And I am on "/fiche/projet/1"

        Then I should see "Contenu du fait marquant déjà créé"

        When I click on the 1st ".timeline a:contains('Modifier')" element
        And I press "Supprimer"
        Then I should see "Le fait marquant \"Fait marquant déjà créé\" a été supprimé"
        But I should not see "Contenu du fait marquant déjà créé"

    Scenario: Un contributeur ne peut pas modifier les faits marquants des autres contributeurs
        Given I am on "/connexion"
        And I fill in the following:
            | _username | contributeur2@societe.dev  |
            | _password | contributeur2              |
        And I press "Connexion"
        And I am on "/fiche/projet/1"

        When I click on the 1st ".timeline a:contains('Modifier')" element
        Then I should not see "Modifiez votre fait marquant" in the "h1" element

    Scenario: Un chef de projet peut modifier les faits marquants des autres contributeurs
        Given I am on "/connexion"
        And I fill in the following:
            | _username | cdp@societe.dev  |
            | _password | cdp              |
        And I press "Connexion"
        And I am on "/fiche/projet/1"

        When I click on the 1st ".timeline a:contains('Modifier')" element
        Then I should see "Modifiez votre fait marquant" in the "h1" element