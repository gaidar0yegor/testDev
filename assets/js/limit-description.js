// On selectionne l'element textarea et l'élement p#counterBlock
var textarea = document.querySelector('#fait_marquant_description');
var blockCount = document.getElementById('count-carac');

function mb_strlen(str) {
    var len = 0;
    for(var i = 0; i < str.length; i++) {
        len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? 2 : 1;
    }
    return len;
}

function countChars(str){
    return str.split('').length;
}
var carac_php = 2;
function count() {
    // la fonction count calcule la longueur de la chaîne de caractère contenue dans le textarea
    line_feed = textarea.value.charCodeAt(textarea.value.length-1);
    carriage_return = textarea.value.charCodeAt(textarea.value.length-2);

    var count = 750-textarea.value.length;
    
    if(line_feed == 10 || carriage_return == 10) {
        
        count = count - carac_php;
    }

    
    console.log(carac_php);        
    console.log(line_feed);        
    console.log(carriage_return);       
    // console.log(carac_php);  
    console.log(count); 
    //console.log(countChars(t);
    blockCount.innerHTML= count;

   // si le count descend sous 0 on ajoute la class red à la balise p#counterBlock
   if (count<0) {
    	blockCount.classList.add("text-danger");
   }
   else if(count>=0) {
     	blockCount.classList.remove("text-danger");
   }
   
}


// on pose un écouteur d'évènement keyup sur le textarea.
// On déclenche la fonction count quand l'évènement se produit et au chargement de la page
textarea.addEventListener('keyup', count);
count();