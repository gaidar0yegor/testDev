Feature: Multi-société, page de changement de société

    Background:
        Given I have loaded fixtures from "multi_societe/switch.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | user@societe.dev  |
            | _password | user              |
        And I press "Connexion"

#    Activer Cahier de labo
#    Scenario: J'arrive sur la page de switch de société si j'ai plusieurs société et que je ne suis encore connecté à aucune d'entre elles.
##        Then I should be on "/mes-plateformes"
##        When I go to "/corp/mes-societes"
##        And I should see "Mes sociétés" in the "h1" element
##        And I should see "User" in the "nav" element
##        And I should see "Société0"
##        And I should see "Aller sur Société0"
##        And I should see "Société1"
##        And I should see "Aller sur Société1"

    Scenario: J'arrive sur la page de switch de société si j'ai plusieurs société et que je ne suis encore connecté à aucune d'entre elles.
        Then I should be on "/corp/mes-societes"
        And I should see "Mes sociétés" in the "h1" element
        And I should see "User" in the "nav" element
        And I should see "Société0"
        And I should see "Aller sur Société0"
        And I should see "Société1"
        And I should see "Aller sur Société1"

    Scenario: Je peux me connecter à une société, et ensuite switcher sur l'autre.
        When I go to "/corp/mes-societes"
        And I press "Aller sur Société1"
        Then I should see "Société1" in the "nav" element
        And I should see "User" in the "nav" element

        When I follow "Voir plus ..."
        And I press "Aller sur Société0"
        Then I should see "Société0" in the "nav" element
        And I should see "User" in the "nav" element

#    Activer Cahier de labo
#    Scenario: Je peux consulter et modifier mes informations personnelles même en étant pas connecté sur une société
#        Then I should be on "/mes-plateformes"
#        When I go to "/corp/mes-societes"
#        And I follow "Mon compte"
#        Then I should see "Mon compte" in the "h1" element
#        And I should see "Nom Eureka"
#        And I should see "Email user@societe.dev"
#        And I should see "Mes notifications"
#
#        When I follow "Mettre à jour"
#        Then I should see "Modification de mon compte"
#        When I fill in the following:
#            | Prénom | NouveauPrenom |
#        And I press "Mettre à jour"
#        Then I should find toastr message "Vos informations personnelles ont été mises à jour"
#        And I should see "NouveauPrenom" in the "nav" element

    Scenario: Je peux consulter et modifier mes informations personnelles même en étant pas connecté sur une société
        When I follow "Mon compte"
        Then I should see "Mon compte" in the "h1" element
        And I should see "Nom Eureka"
        And I should see "Email user@societe.dev"
        And I should see "Mes notifications"

        When I follow "Mettre à jour"
        Then I should see "Modification de mon compte"
        When I fill in the following:
            | Prénom | NouveauPrenom |
        And I press "Mettre à jour"
        Then I should find toastr message "Vos informations personnelles ont été mises à jour"
        And I should see "NouveauPrenom" in the "nav" element

    Scenario: Je ne peux pas switcher sur une société dont mon accès a été désactivé par l'admin
        When I go to "/corp/mes-societes"
        Then I should not see "Aller sur SociétéDisabled"
        But I should see "Accès désactivé" in the ".card" element containing "SociétéDisabled"

#    Activer Cahier de labo
#    Scenario: Je ne peux pas switcher sur une société dont mon accès a été désactivé par l'admin, même en le faisant par une requête POST
#        When I send a POST request to "/corp/mes-societes/3"
#        And I go to the homepage
#        Then I should not see "Mon tableau de bord"
#        But I should be on "/mes-plateformes"
#        And I should see "Bienvenue sur le portail de RDI Manager" in the "h1" element

    Scenario: Je ne peux pas switcher sur une société dont mon accès a été désactivé par l'admin, même en le faisant par une requête POST
        When I send a POST request to "/corp/mes-societes/3"
        And I go to the homepage
        Then I should not see "Mon tableau de bord"
        But I should be on "/corp/mes-societes"
        And I should see "Mes sociétés" in the "h1" element

#    Activer Cahier de labo
#    Scenario: Je ne peux pas usurper l'accès de quelqu'un d'autre
#        When I send a POST request to "/corp/mes-societes/4"
#        And I go to the homepage
#        Then I should not see "Mon tableau de bord"
#        But I should be on "/mes-plateformes"
#        And I should see "Bienvenue sur le portail de RDI Manager" in the "h1" element

    Scenario: Je ne peux pas usurper l'accès de quelqu'un d'autre
        When I send a POST request to "/corp/mes-societes/4"
        And I go to the homepage
        Then I should not see "Mon tableau de bord"
        But I should be on "/corp/mes-societes"
        And I should see "Mes sociétés" in the "h1" element

#    Activer Cahier de labo
#    Scenario: Je suis déconnecté de ma société lorsque mon accès a été désactivé
#        When I send a POST request to "/corp/mes-societes/4"
#        And I go to the homepage
#        Then I should not see "Mon tableau de bord"
#        But I should be on "/mes-plateformes"
#        And I should see "Bienvenue sur le portail de RDI Manager" in the "h1" element

    Scenario: Je suis déconnecté de ma société lorsque mon accès a été désactivé
        When I send a POST request to "/corp/mes-societes/4"
        And I go to the homepage
        Then I should not see "Mon tableau de bord"
        But I should be on "/corp/mes-societes"
        And I should see "Mes sociétés" in the "h1" element
