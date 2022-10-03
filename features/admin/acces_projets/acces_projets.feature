Feature: Pouvoir modifier les rôle d'un utilisateur sur tous ses projets

    Background:
        Given I have loaded fixtures from "admin/acces_projets/fixtures.yml"

        Given I am on "/connexion"
        And I fill in the following:
            | _username | admin@societe.dev  |
            | _password | admin              |
        And I press "Connexion"

    Scenario: L'admin peut accéder au formulaire de modification des rôles d'un user sur tous ses projets
        When I follow "Utilisateurs"
        And I click on the 1st "[href='/corp/utilisateur/3']" element
        And I follow "Gérer ses accès aux projets"
        Then I should be on "/corp/mon_equipe/utilisateurs/3/roles-projets"
        And I should see "Rôles sur projets de Utilisateur Eureka" in the "h1" element
        And I should see "PTEST"
        And I should see "Aucun"
        And I should see "Chef de projet"
        And I should see "Contributeur"
        And I should see "Observateur"
        And I should see "Mettre à jour"

    Scenario: L'admin peut mettre un utilisateur en tant que contributeur depuis la page de ses rôles sur projets
        Given I am on "/corp/mon_equipe/utilisateurs/3/roles-projets"
        When I fill in the following:
            | societe_user_projets_roles[projetParticipants][0][role] | PROJET_CONTRIBUTEUR |
        And I press "Mettre à jour"
        Then I should find toastr message "Les rôles de Utilisateur Eureka sur les projets ont été mis à jour"

    Scenario: L'admin ne peut pas mettre un utilisateur en tant que chef de projet si il y a déjà un autre chef de projet
        Given I am on "/corp/mon_equipe/utilisateurs/3/roles-projets"
        When I fill in the following:
            | societe_user_projets_roles[projetParticipants][0][role] | PROJET_CDP |
        And I press "Mettre à jour"
        Then I should find toastr message "Les rôles n'ont pas été mis à jour à cause d'une incohérence"
        Then I should see "Il doit y avoir un seul chef de projet sur ce projet, vous en avez plusieurs"

    Scenario: L'admin ne peut pas retirer le chef de projet
        Given I am on "/corp/mon_equipe/utilisateurs/2/roles-projets"
        When I fill in the following:
            | societe_user_projets_roles[projetParticipants][0][role] | PROJET_CONTRIBUTEUR |
        And I press "Mettre à jour"
        Then I should find toastr message "Les rôles n'ont pas été mis à jour à cause d'une incohérence"
        Then I should see "Il doit y avoir un chef de projet sur ce projet, vous n'en avez mis aucun"

    Scenario: L'admin peut retirer un participant en mettant son rôle à "Aucun"
        Given I am on "/corp/mon_equipe/utilisateurs/1/roles-projets"
        When I fill in the following:
            | societe_user_projets_roles[projetParticipants][0][role] |  |
        And I press "Mettre à jour"
        Then I should find toastr message "Les rôles de Admin Eureka sur les projets ont été mis à jour"
        When I follow "PTEST"
        Then I should see "Contributeurs (1)"
