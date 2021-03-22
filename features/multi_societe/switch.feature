Feature: Multi-société, page de changement de société

    Background:
        Given I have loaded fixtures from "multi_societe/switch.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

    Scenario: J'arrive sur la page de switch de société si j'ai plusieurs société et que je ne suis encore connecté à aucune d'entre elles.
        Then I should be on "/mes-societes"
        And I should see "Mes sociétés" in the "h1" element
        And I should see "User" in the "nav" element
        And I should see "Société0"
        And I should see "Aller sur Société0"
        And I should see "Société1"
        And I should see "Aller sur Société1"

    Scenario: Je peux me connecter à une société, et ensuite switcher sur l'autre.
        When I go to "/mes-societes"
        And I press "Aller sur Société1"
        Then I should see "Société1 | User" in the "nav" element

        When I follow "Changer de société"
        And I press "Aller sur Société0"
        Then I should see "Société0 | User" in the "nav" element

    Scenario: Je suis déjà connecté à la société sur laquelle j'étais lorsque je me reconnecte.
        Then I should be on "/mes-societes"
        And I press "Aller sur Société1"
        Then I should see "Société1 | User" in the "nav" element

        When I follow "Déconnexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"
        Then I should see "Société1 | User" in the "nav" element

    Scenario: Je peux consulter et modifier mes informations personnelles même en étant pas connecté sur une société
        When I follow "Mon compte"
        Then I should see "Mon compte" in the "h1" element
        And I should see "Nom : Eureka"
        And I should see "E-mail : user@societe.dev"
        And I should see "Mes notifications"

        When I follow "Mettre à jour"
        Then I should see "Modification de mon compte"
        When I fill in the following:
            | Prenom | NouveauPrenom |
        And I press "Mettre à jour"
        Then I should see "Vos informations personnelles ont été mises à jour"
        And I should see "NouveauPrenom" in the "nav" element
