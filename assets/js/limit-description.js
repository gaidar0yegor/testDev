// On selectionne l'element textarea et l'élement p#counterBlock
var textarea = document.querySelector('#fait_marquant_description');
var blockCount = document.getElementById('count-carac');

function count() {
    // la fonction count calcule la longueur de la chaîne de caractère contenue dans le textarea
    var count = 750-textarea.value.length;
    // et affche cette valeur dans la balise p#counterBlock grâce à innerHTML
    blockCount.innerHTML= count;

   // si le count descend sous 0 on ajoute la class red à la balise p#counterBlock
   if(count<0) {
    	blockCount.classList.add("text-danger");
   }
   else if(count>=0) {
     	blockCount.classList.remove("text-danger");
   }
   else{}
}

// on pose un écouteur d'évènement keyup sur le textarea.
// On déclenche la fonction count quand l'évènement se produit et au chargement de la page
textarea.addEventListener('keyup', count);
count();