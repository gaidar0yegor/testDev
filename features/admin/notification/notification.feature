Feature: L'admin peut personnaliser le jour et l'heure d'envoi des notifications.

    Background:
        Given I have loaded fixtures from "admin/notification/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"

    Scenario: L'admin peut accéder et modifier le jour et l'heure d'envoi des notifications
        When I follow "Paramètres notifications"
        Then I should see "Paramètres notifications" in the "h1" element

        And I should see "Utiliser les notifications SMS"

        And I should see "Rappel pour créer les faits marquants"
        And I should see "Liste des derniers faits marquants ajoutés"
        And I should see "Rappel pour saisir nos temps et absences"

        When I press "Mettre à jour"
        Then I should find toastr message "Vos préférences de notifications ont été mises à jour"

    Scenario: L'admin peut voir les dernières notifications envoyées
        When I follow "Paramètres notifications"
        And I follow "Dernières notifications envoyées"
        Then I should see "Dernières notifications envoyées" in the "h1" element
        And I should see "Run successfully" in the "9 nov. 2020" row
