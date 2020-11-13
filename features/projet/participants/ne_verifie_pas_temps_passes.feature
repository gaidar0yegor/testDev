Feature: Vérifie que lors de la modification des rôles des participants
    à un projet, la validation de lève pas d'erreur si la somme
    des pourcentages des temps passés de tous les participants
    dépasse 100%.

    Scenario: Chef de projet peut mettre à jour les contributeurs sans avoir l'erreur "Pourcentages > 100%"
        Given I have loaded fixtures from "projet/participants/ne_verifie_pas_temps_passes.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | cdp@societe.dev  |
            | _password | cdp              |
        And I press "Connexion"

        When I go to "/projets"
        And I follow "P"
        And I follow "Participants"
        And I press "Mettre à jour"
        Then I should not see "La somme des pourcentages doit être entre 0 et 100, 150 obtenu."
        And I should see "Les rôles des participants ont été mis à jour"
