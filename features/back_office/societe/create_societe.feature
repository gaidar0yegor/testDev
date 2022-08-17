Feature: Création d'une nouvelle société

    Background:
        Given I have loaded fixtures from "back_office/societe/create_societe.yml"
        And I am on "/connexion"
        When I fill in the following:
            | _username | user@bo.dev |
            | _password | user        |
        And I press "Connexion"

    Scenario: Je peux créer une nouvelle société
        When I follow "Sociétés"
        And I follow "Créer une nouvelle société"
        Then I should see "Créer une nouvelle société" in the "h1" element

        When I fill in the following:
            | Raison sociale                 | SociétéTest          |
            | Email du nouvel administrateur | test@societetest.com |
        And I press "Initialiser la société"
        Then I should find toastr message "La société \"SociétéTest\" a bien été créée"
        And I should see "En attente de finalisation d'inscription"
        And I should see "Invitation pas encore envoyée"
