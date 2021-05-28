# SMS

Ce dossier contient les templates des SMS qui sont envoyés par RDI-Manager.

## Contraintes

Étant donné que ce ne sont pas le nombre de SMS qui est facturé, mais le nombre de segment de SMS (un segment = ~1120 bit, soit 160 charactères), il faut limiter la taille des SMS pour que dans l'idéal un seul segment soit envoyé par SMS.

Les charactères spéciaux peuvent aussi contribuer à augmenter de beaucoup la taille du SMS. Si un charactère spécial est utilisé, l'encodage utilisé changera, et peut doubler le nombre de segments envoyés.

### Simulateur de taille SMS

Vérifier le nombre de segments envoyés et la présence de charactères spéciaux grâce à ce simulateur :

https://twiliodeved.github.io/message-segment-calculator/

### Plus d'infos

Voir plus d'info sur les SMS/segments/encodage... ici : https://www.twilio.com/blog/2017/03/what-the-heck-is-a-segment.html
