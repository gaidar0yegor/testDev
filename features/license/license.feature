Feature: Limiter l'utilisation de RDI-Manager dans le temps et le nombre de contributeurs et de projets, afin de rappeller de renouveller son abonnement.

    Background:
        Given I have loaded fixtures from "license/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | cdp@societe.dev  |
            | _password | cdp              |
        And I press "Connexion"

    Scenario: Je peux créer un nouveau projet tant que je ne dépasse pas mon quota
        Given societe "Société" reset licenses
        And societe "Société" has a license with "2" projets and "10" contributeurs

        And I am on "/corp/projets"
        When I follow "Créer un projet"
        Then I should be on "/corp/projets/creation"
        When I fill in the following:
            | projet_form[acronyme] | PTEST2        |
            | projet_form[titre]    | Projet Test 2 |
        And I press "Soumettre"
        Then I should find toastr message "Le projet \"Projet Test 2\" a été créé"

    Scenario: Je ne peux pas créer de nouveau projet si ca dépasse mon quota
        Given societe "Société" reset licenses
        And societe "Société" has a license with "1" projets and "10" contributeurs

        When I am on "/corp/projets"
        And I follow "Créer un projet"
        Then I should be on "/corp/projets/creation"
        When I fill in the following:
            | projet_form[acronyme] | PTEST2        |
            | projet_form[titre]    | Projet Test 2 |
        And I press "Soumettre"
        Then I should not see "Le projet \"Projet Test 2\" a été créé"
        But I should find toastr message "Vous ne pouvez pas faire cette action car ça dépasserait votre quota \"activeProjet\" qui sera alors de 2 sur 1"

    Scenario: Je ne peux pas ajouter de contributeur si ca dépasse mon quota de contributeurs
        Given societe "Société" reset licenses
        And societe "Société" has a license with "1" projets and "1" contributeurs

        When I go to "/corp/projets"
        And I follow "PTEST"
        And I follow "Gérer les participants"
        And I fill in the following:
            | liste_projet_participants[projetParticipants][1][role] | PROJET_CONTRIBUTEUR |
        And I press "Mettre à jour"
        Then I should find toastr message "Vous ne pouvez pas faire cette action car ça dépasserait votre quota \"contributeurs\" qui sera alors de 2 sur 1"

    Scenario: Je peux ajouter un fait marquant si mes licenses sont valides
        Given societe "Société" reset licenses
        And societe "Société" has a license with "1" projets and "1" contributeurs

        When I go to "/corp/projets"
        And I follow "PTEST"
        And I follow "Ajouter un fait marquant dans un nouvel onglet"
        And I fill in the following:
            | fait_marquant[titre]       | Mon fait marquant          |
            | fait_marquant[description] | J'ai créé un fait marquant |
        And I press "Publier"
        Then I should find toastr message "Le fait marquant \"Mon fait marquant\" a été ajouté au projet"

    Scenario: Je ne peux pas ajouter de fait marquant si je n'ai aucune license
        Given societe "Société" reset licenses

        When I go to "/corp/projets"
        And I follow "PTEST"
        And I follow "Ajouter un fait marquant dans un nouvel onglet"
        And I fill in the following:
            | fait_marquant[titre]       | Mon fait marquant          |
            | fait_marquant[description] | J'ai créé un fait marquant |
        And I press "Publier"
        Then I should not see "Le fait marquant \"Mon fait marquant\" a été ajouté au projet"
        But I should find toastr message "Votre accès est en lecture seule car un ou plusieurs quotas de vos licenses actives ont été dépassés. Veuillez ajouter une nouvelle license."

    Scenario: Je ne peux pas ajouter de fait marquant si mes licenses sont toutes expirées
        Given societe "Société" reset licenses
        And societe "Société" has an expired license

        When I go to "/corp/projets"
        And I follow "PTEST"
        And I follow "Ajouter un fait marquant dans un nouvel onglet"
        And I fill in the following:
            | fait_marquant[titre]       | Mon fait marquant          |
            | fait_marquant[description] | J'ai créé un fait marquant |
        And I press "Publier"
        Then I should not see "Le fait marquant \"Mon fait marquant\" a été ajouté au projet"
        But I should find toastr message "Votre accès est en lecture seule car un ou plusieurs quotas de vos licenses actives ont été dépassés. Veuillez ajouter une nouvelle license."
