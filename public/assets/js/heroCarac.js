function showCarac()
{              
  document.getElementById('combat').setAttribute('value', (Math.floor(Math.random() * 10) +10) );
  document.getElementById('endurance').setAttribute('value', (Math.floor(Math.random() * 10) +20)  );
  document.getElementById('gold').setAttribute('value', Math.floor(Math.random() * 10) );
  document.getElementById('roll').setAttribute('hidden',true);
  
}

// copier les valeurs dans les champs correspondant
$(document).ready(function(){
  $("#rolldice").click(function(){
    document.getElementById('new_adventure_endMax').setAttribute('value',($("#endurance").val()) );
    document.getElementById('new_adventure_endurance').setAttribute('value',($("#endurance").val()) );
    document.getElementById('new_adventure_combatSkill').setAttribute('value',($("#combat").val()) );
    document.getElementById('new_adventure_gold').setAttribute('value',($("#gold").val()) );
  });
});