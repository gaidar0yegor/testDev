Feature: Au lieu d'afficher une erreur obscure, afficher une page d'erreur personnalisée avec des liens de retours.

    Scenario: Page d'erreur 404 personnalisée
        When I go to "/page-qui-n-existe-pas"
        Then I should not see "Oops! An Error Occurred"
        But I should see "Oups, il semblerait que cette page n'existe pas"
        And I should see " Vous pouvez soit revenir à la page précédente, soit revenir sur la page d'accueil"
