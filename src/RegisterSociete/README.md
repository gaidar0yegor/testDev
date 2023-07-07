# Inscription et création d'un nouvelle société

Permet à tout le monde de se créer une nouvelle société de manière autonome,
et un nouveau compte RDI-Manager.

## Description

Accessible sous l'url `/creer-ma-societe`, un anonyme peut créer une nouvelle
société, ou reprendre son inscription.

C'est en plusieurs étapes :

- Ma société : créer la société
- Mon compte : créer le compte RDI-Manager et le lie à la société avec un rôle admin
- Mon projet (optionelle) : créer un premier projet
- Mes collaborateurs (optionelle) : permet d'inviter 2 premiers collaborateurs à cette société

En allant sur `/creer-ma-societe`, on est redirigé vers la prochaine étape,
en fonction de l'état d'avancement de l'inscription en cours,
qui est stockée en session si le compte n'est pas encore créé,
ou en fonction des projets déjà créés et des collaborateurs déjà invités dans la société.

## Technique

Le controller `App\FO\RegisterController` gère les pages des étapes :
 - la méthode `getExpectedRoutes` retourne la liste des pages possible
en fonction de l'état actuel de l'inscription
 - la méthode `shouldRedirect` retourne la prochaine étape,
ou qui indique si l'étape en cours est déjà terminée et si on devrait
rediriger l'utilisateur vers la prochaine.
