Feature: Pouvoir ajouter un lien vers Trello, ou d'autres liens externes en général sur la page projet

    Background:
        Given I have loaded fixtures from "projet/lien_externe/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

    Scenario: Un lien s'affiche avec un texte personnalisé
        When I go to "/corp/projets/1"
        Then I should see an "i.fa-trello" element
        When I follow "Trello board"
        Then I should be on "https://trello.com/b/DEj9Z6fX/rdi-manager"

    Scenario: Un lien s'affiche en brut si pas de texte personnalisé
        When I go to "/corp/projets/1"
        Then I should see an "i.fa-github" element
        When I follow "https://github.com/RDI-Manager/rdi-manager.github.io"
        Then I should be on "https://github.com/RDI-Manager/rdi-manager.github.io"

    Scenario: Un lien vers un domaine inconnu s'affiche aussi avec l'icône par défaut
        When I go to "/corp/projets/1"
        Then I should see "http://localhost"
        And I should see an "i.fa-external-link" element
