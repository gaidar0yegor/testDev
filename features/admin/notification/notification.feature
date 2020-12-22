Feature: L'admin peut personnaliser le jour et l'heure d'envoi des notifications.

    Background:
        Given I have loaded fixtures from "admin/notification/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"

    Scenario: L'admin peut accéder et modifier le jour et l'heure d'envoi des notifications
        When I follow "Paramètres des notifications"
        Then I should see "Paramètres des notifications" in the "h1" element

        And I should see "Utiliser les notifications SMS"

        And I should see "Rappel pour créer les faits marquants"
        And I should see "Liste des derniers faits marquants ajoutés"
        And I should see "Rappel pour saisir nos temps et absences"

        When I press "Enregistrer"
        Then I should see "Vos préférences de notifications ont été mises à jour"
