Feature: Saisie des temps passés en pourcentage sur les projets dont l'utilisateur participe.

    Background:
        Given I have loaded fixtures from "temps/api_saisie_temps.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

    Scenario: On peut récupérer un nouveau cra mensuel avec des temps initialisés à 0
        When I am on "/corp/api/temps/2021/05"
        Then the response should be in JSON
        And the JSON should be equal to:
            """
                {
                    "tempsPassesModifiedAt": null,
                    "tempsPasses": [
                        {
                            "id": null,
                            "projet": {
                                "id": 1,
                                "titre": "Projet de test 1",
                                "acronyme": "P1",
                                "colorCode": "#e9ece6"
                            },
                            "pourcentage": 0,
                            "pourcentageMin": 0
                        },
                        {
                            "id": null,
                            "projet": {
                                "id": 2,
                                "titre": "Projet de test 2",
                                "acronyme": "P2",
                                "colorCode": "#e9ece6"
                            },
                            "pourcentage": 0,
                            "pourcentageMin": 0
                        },
                        {
                            "id": null,
                            "projet": {
                                "id": 3,
                                "titre": "Projet de test 3",
                                "acronyme": "P3",
                                "colorCode": "#e9ece6"
                            },
                            "pourcentage": 0,
                            "pourcentageMin": 0
                        }
                    ],
                    "isUserBelongingToSociete":false
                }
            """

    Scenario: On peut soumettre un nouveau cra mensuel
        When I send a POST request to "/corp/api/temps/2021/05" with body:
            """
                [
                    [1, 10],
                    [2, 40],
                    [3, 50]
                ]
            """
        Then the response status code should be 204

        When I am on "/corp/api/temps/2021/05"
        Then the response should be in JSON
        And the JSON nodes should be equal to:
            | tempsPasses[0].id          | 1  |
            | tempsPasses[0].pourcentage | 10 |
            | tempsPasses[1].id          | 2  |
            | tempsPasses[1].pourcentage | 40 |
            | tempsPasses[2].id          | 3  |
            | tempsPasses[2].pourcentage | 50 |
        And the JSON node "tempsPassesModifiedAt" should not be null

    Scenario: On ne peut pas soumettre un pourcentage > 100
        When I send a POST request to "/corp/api/temps/2021/05" with body:
            """
                [
                    [1, 120],
                    [2, 0],
                    [3, 0]
                ]
            """
        Then the response status code should be 400

    Scenario: On ne peut pas soumettre des pourcentages dont le total > 100
        When I send a POST request to "/corp/api/temps/2021/05" with body:
            """
                [
                    [1, 50],
                    [2, 50],
                    [3, 50]
                ]
            """
        Then the response status code should be 400
