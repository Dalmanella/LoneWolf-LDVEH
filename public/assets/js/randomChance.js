
function randomLuck(){
        let luck = Math.floor(Math.random() * 10);

        if (luck > 7){
            
            document.getElementById('btn-luck2').setAttribute('style', "display:none;" );
            document.getElementById('luck2-s').setAttribute('style', "display:block;" );
        } 
        else{
            
            document.getElementById('btn-luck2').setAttribute('style', "display:none;" );
            document.getElementById('luck2-f').setAttribute('style', "display:block;" );
        }
    }

