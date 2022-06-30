<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

//pages visitées
class HealingOverTime {

    public function __construct(
        EntityManagerInterface $entityManager)
        {
            $this->entityManager = $entityManager;
        }

    public function healing($p,$pagesVues,$hero){
        $isHealing=$hero[0]->isKaiHeal();
        if($isHealing){
            if (in_array($p, $pagesVues,false)==false){
                // Healing 
                $endurance = $hero[0]->getEndurance();
                $endM= $hero[0]->getEndMax();
                    
                if( $endurance<$endM){
                    $endurance+=1;
                    $hero[0]->setEndurance($endurance);
                        
                    $this->entityManager->flush();
                }
            };
        };
       
    }

}