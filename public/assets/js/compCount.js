// verif du nombre de compétences cochées et activation du bouton de validation du hero
function updateCount() {
    let count = $(".kaicount:checked").length;
                
    if (count!==5){
      document.getElementById('validation_bouton').setAttribute('style',"display:none;");
    }else{
      document.getElementById('validation_bouton').setAttribute('style',"display:block;");
    }                             
  };          

  function randomLast(){
    let lucky = Math.floor(Math.random() * 10);

    if (lucky == 9){
        document.getElementById('btn-last-chance').setAttribute('style', "display:none;" );
        document.getElementById('last-chance-s').setAttribute('style', "display:'block';" );
    } 
    else{
        document.getElementById('btn-last-chance').setAttribute('style', "display:none;" );
        document.getElementById('last-chance-f').setAttribute('style', "display:'block';" );
    }
}