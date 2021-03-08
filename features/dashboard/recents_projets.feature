Feature: Mes projets récents

    Background:
        Given I have loaded fixtures from "dashboard/recents_projets.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | cdp@societe.dev  |
            | _password | cdp              |
        And I press "Connexion"

    Scenario: L'API retourne les données
        When I am on "/api/dashboard/recents-projets"
        Then the response should be in JSON
        And the JSON should be equal to:
            """
                {
                    "recentsProjets": [
                        {
                            "id": 3,
                            "acronyme": "P3",
                            "activities": [
                                {
                                    "text": "activity_2"
                                }
                            ]
                        },
                        {
                            "id": 4,
                            "acronyme": "P4",
                            "activities": [
                                {
                                    "text": "activity_3"
                                }
                            ]
                        }
                    ]
                }
            """
