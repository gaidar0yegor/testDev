Feature: S'inscrire en tant que société.

    Afin de permettre à de nouveaux administrateurs de s'inscrire automatiquement,
    le tunnel d'inscription permet de se créer un compte et une société,
    et d'ajouter notre projet et les premiers collaborateurs.

    Scenario: Inscription d'une société et compte admin, partie anonyme
        Given I have loaded fixtures from "empty_database.yml"

        When I go to "/corp/creer-ma-societe"
        Then I should see "Bienvenue sur RDI-Manager, vous allez ici créer votre société et votre accès administrateur afin de suivre vos projets"

        When I fill in the following:
            | societe[raisonSociale] | MaSocieteTest |
        And I press "Continuer"
        Then I should see "Mon compte RDI-Manager"

        When I follow "Créer mon compte RDI-Manager"
        And I fill in the following:
            | account[email]            | admin@societe.dev |
            | account[prenom]           | PrénomAdmin       |
            | account[nom]              | NomAdmin          |
            | account[password][first]  | passtest          |
            | account[password][second] | passtest          |
        And I press "Continuer"
        Then I should see "Vous avez reçu un code de vérification à 6 chiffres sur votre boîte email. Veuillez le saisir ici"

        When I follow "Revérifier mon adresse email"
        Then I should see "Création de votre compte administrateur sur la société MaSocieteTest"

    Scenario: Ajout de mon premier projet et collaborateurs
        Given I have loaded fixtures from "register_societe/admin_cree.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"
        And I am on "/corp/creer-ma-societe/mon-projet"
        And I should see "Créer mon projet" in the "h1" element

        When I fill in the following:
            | projet[titre]    | ProjetTest |
            | projet[acronyme] | PTEST      |
        And I press "Continuer"
        Then I should see "Inviter mes collaborateurs" in the "h1" element

        When I fill in the following:
            | collaborators[email0] | user0@societe.dev |
            | collaborators[role0]  | SOCIETE_USER      |
            | collaborators[email1] | user1@societe.dev |
            | collaborators[role1]  | SOCIETE_USER      |
        And I press "Continuer"
        Then I should see "Votre compte RDI-Manager est maintenant créé"

        When I follow "Aller sur mon tableau de bord"
        Then I should see "Mon tableau de bord"

        When I follow "Projets"
        Then I should see "PTEST"

        When I follow "Utilisateurs"
        Then I should see "user0@societe.dev"
        And I should see "user1@societe.dev"
