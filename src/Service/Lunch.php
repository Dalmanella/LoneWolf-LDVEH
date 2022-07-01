<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Hero;
use App\Repository\BackpackRepository;


class Lunch {

    public function __construct(
        EntityManagerInterface $entityManager,
        BackpackRepository $backpackRep)
        {
            $this->entityManager = $entityManager;
            $this->backpackRep = $backpackRep;
        }

    public function Lunch($heroId){
        
        $hero= $this->entityManager->getRepository(Hero::class) -> findBy(['id'=>$heroId]);
        $inventaire= $this->backpackRep-> findBy(['heroId'=>$hero]);                //recup de l'inventaire
        
        $items = $inventaire[0]->getObjetId()->getValues();
        
       
        
            if($hero[0]->isKaiHunt() == false) {        //si pas la comp chasse
                $mealCountStart= 0;
                $mealCount=0; 
                $end=$hero[0]->getEndurance();
                
                for($i=0; $i < count($items); $i++ ){       // on compte les repas dans l'inventaire
                        
                    if($items[$i]->getObjType() == "meal"){ 
                        $mealCountStart+= 1;
                        
                    };
                };               
               
                
                if($mealCountStart == 0 && $hero[0]->isLunchClick() == 0){ 
                                                                  // si pas de repas en stock
                    $newEnd = $hero[0]->setEndurance($end-3);   // on retire  3 pts d'endurance
                    $this->entityManager->flush($newEnd);
                    
                    $lunch= $hero[0]->setLunchClick(1);            // et on valide le dejeuner
                    $this->entityManager->flush($lunch); 
                
                }
           
            }

            $isLunch = $hero[0]->isLunchClick();
            
            return $isLunch;
    }
}