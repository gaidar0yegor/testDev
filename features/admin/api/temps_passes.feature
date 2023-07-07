Feature: API qui donne les pourcentages des temps passés
    d'un user, sur ses projets, sur une année.
    Sert pour le graphique dans la page admin d'un user.

    Background:
        Given I have loaded fixtures from "admin/api/temps_passes.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"

    Scenario: Le référent peut voir toutes les infos d'un utilisateur en particulier
        When I am on "/corp/api/stats/temps-par-projet/1/2020/percent"
        Then the response should be in JSON
        And the JSON node "months[10].PT" should be equal to "80"

    Scenario: Le référent peut voir toutes les infos d'un projet en particulier
        When I am on "/corp/api/stats/temps-par-user/1/2020/percent"
        Then the response should be in JSON
        And the JSON should be equal to:
            """
                {
                    "months": [
                        [],
                        [],
                        [],
                        [],
                        [],
                        [],
                        [],
                        [],
                        [],
                        [],
                        {
                            "A. Eureka": 80
                        },
                        []
                    ]
                }
            """
