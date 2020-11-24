Feature: Affichage de la saisie des temps pour un utilisateur qui n'a pas de projet en ce moment

    Scenario: Affichage d'un message explicatif, mais navigation entre les mois toujours possible
        Given I have loaded fixtures from "temps/user_sans_projet.yml"

        And I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

        When I am on "/temps/2020/01"
        Then I should see "Saisie de temps passé"
        And I should see "Ce mois-ci, vous n'avez pas de temps à saisir car vous n'étiez contributeur sur aucun projet"
        And I should see "Janvier 2020"
        And I should see an "a[href='/temps/2020/02']" element
        And I should see an "a[href='/temps/2019/12']" element
