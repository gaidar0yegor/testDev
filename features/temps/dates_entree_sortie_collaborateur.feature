Feature: L'utilisateur ayant une date d'entrée et de sortie dans la société,
    il est rappellé qu'il n'est pas ou plus dans la société
    quand il va sur certain mois pour valider ses temps.

    Background:
        Given I have loaded fixtures from "temps/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user2@societe.dev  |
            | _password | user2              |
        And I press "Connexion"

    Scenario: J'ai un rappel pour dire si je ne suis pas/plus dans la société
        When I go to "/temps/2020/02"
        Then I should see "Ce mois-ci, vous n'êtes pas encore dans la société"
        When I go to "/temps/2020/09"
        Then I should see "Ce mois-ci, vous n'êtes plus dans la société"
        When I go to "/temps/2020/05"
        Then I should not see "Ce mois-ci, vous n'êtes pas encore dans la société"
        Then I should not see "Ce mois-ci, vous n'êtes plus dans la société"

        When I go to "/absences/2020/02"
        Then I should see "Ce mois-ci, vous n'êtes pas encore dans la société"
        When I go to "/absences/2020/09"
        Then I should see "Ce mois-ci, vous n'êtes plus dans la société"
        When I go to "/absences/2020/05"
        Then I should not see "Ce mois-ci, vous n'êtes pas encore dans la société"
        Then I should not see "Ce mois-ci, vous n'êtes plus dans la société"
