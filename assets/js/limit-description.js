// On selectionne l'element textarea et l'élement p#counterBlock
var textarea = document.querySelector('#fait_marquant_description');
var blockCount = document.getElementById('count-carac');
var faitMarquantMaxDesc = document.getElementById('fait_marquant_max_desc');

function count() {
    var limit = parseInt(faitMarquantMaxDesc.dataset.limit);
    var limitBlocking = parseInt(faitMarquantMaxDesc.dataset.limitblocking);

    // la fonction count calcule la longueur de la chaîne de caractère contenue dans le textarea
    var count = limit === -1 ? textarea.value.replace(/\n/g, '  ').length : limit - textarea.value.replace(/\n/g, '  ').length;

    // et affche cette valeur dans la balise p#counterBlock grâce à innerHTML
    blockCount.innerHTML= count;

   // si le count descend sous 0 on ajoute la class red à la balise p#counterBlock
   if(count<0) {
    	blockCount.classList.add( limitBlocking ? "text-danger" : "text-warning" );
   }
   else if(count>=0) {
     	blockCount.classList.remove("text-danger","text-warning");
   }
}

if (textarea) {
    // on pose un écouteur d'évènement keyup sur le textarea.
    // On déclenche la fonction count quand l'évènement se produit et au chargement de la page
    textarea.addEventListener('change', count);
    count();
}
