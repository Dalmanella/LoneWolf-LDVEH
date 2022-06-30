// tirage d'une arme associée à la compétence Weapon Skill
function kaiWeaponCheck() {
    let isCheck = $("#new_adventure_kaiWeapon:checked").length;
    
    if (isCheck){
        
        document.getElementById('new_adventure_weapon').setAttribute('value', Math.floor(Math.random() * 10) );
        let weapon;
        let weapTag=$("#new_adventure_weapon").val();
      
        switch (weapTag){
            case '0':
            weapon = 'dagger';
            break;
            case '1':
            weapon = 'spear';
            break;
            case '2':
            weapon = 'mace';
            break;
            case '3':
            weapon = 'short sword';
            break;
            case '4':
            weapon = 'warhammer';
            break;
            case '5':
            weapon = 'sword';
            break;
            case '6':
            weapon = 'axe';
            break;
            case '7':
            weapon = 'sword';
            break;
            case '8':
            weapon = 'quarterstaff';
            break;
            case '9':
            weapon = 'broadsword';
            break;
        }
        alert("You are proficient with " + weapon + "." ); 
    }else{
        document.getElementById('new_adventure_weapon').setAttribute('value', 11 );
    }
};
