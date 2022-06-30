
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