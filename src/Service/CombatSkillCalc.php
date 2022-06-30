<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Hero;
use App\Repository\BackpackRepository;
use App\Entity\Ennemy;


class CombatSkillCalc {

    public function __construct(
        EntityManagerInterface $entityManager,        
        BackpackRepository $backpackRep)
        {
            $this->entityManager = $entityManager;
            $this->backpackRep = $backpackRep;
        }

    public function CsCalculation($heroId, $ennemyId){
        
        $hero= $this->entityManager->getRepository(Hero::class) -> findBy(['id'=>$heroId]);
        $ennemy= $this->entityManager->getRepository(Ennemy::class)->findById($ennemyId);
        $inventaire= $this->backpackRep-> findBy(['heroId'=>$hero]);                //recup de l'inventaire
        
        $items = $inventaire[0]->getObjetId()->getValues();
        
        $CS=$hero[0]->getCombatSkill();
            
            //combat à mains nues?
                $weapon=0;
                for($i=0;$i<count($items);$i++){
                    if($items[$i]->getPlace() == "hand" ){
                        $weapon = $weapon+1;
                    }
                };
                if($weapon==0){
                    $CS-=4;
                }
                
            //kai weaponmastery and good weapon in hand
                if($hero[0]->isKaiWeapon() == true){
                    for($i=0;$i<count($items);$i++){
                        if($items[$i]->getWTag()==$hero[0]->getWeapon()){
                            $CS+=2;
                        }
                    }
                }
                
            // mindblast vs mindshield
                if ( ($hero[0]->isKaiMblast()==true) && ($ennemy[0]->isMindshield()==false)){
                    $CS+=2;
                }
                
            // mindshield vs mindforce
                if ( ($hero[0]->isKaiMshield()==false) && ($ennemy[0]->isMindforce()==true)){
                    $CS-=2;
                }
            
            // Combat contre le Burrowcrawler avec/sans torche
            if( $ennemyId == 12){
                $tinder=0;
                $torch=0;

                $items = $inventaire[0]->getObjetId()->getValues();

                for($i=0; $i < count($items); $i++ ){ 
                    if($items[$i]->getId() == 8){ 
                        $torch = 1;                        
                    };
                    if($items[$i]->getId() == 9){ 
                        $tinder = 1;                        
                    };
                }
                if( $tinder == 0 or $torch == 0){
                    $CS-=3;
                }
            }
            
            return $CS;
    }
}