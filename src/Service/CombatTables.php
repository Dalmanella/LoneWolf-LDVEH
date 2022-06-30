<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\CombatEnd;

class CombatTables{
    public function __construct(
        EntityManagerInterface $entityManager
        )
        {
            $this->entityManager = $entityManager;
           
        }

        public function CombatTables($CR,$fight,$endA,$endLw,$coup){
            // table inferieur ou égale à  -11
                if($CR < -10){
                    if($coup == 1){
                        $fight[0]->setEndE($endA-0);
                        $fight[0]->setEndL(0);  
                    }
                    if($coup == 2){
                        $fight[0]->setEndE($endA-0);
                        $fight[0]->setEndL(0);  
                    }
                    if($coup == 3){
                        $fight[0]->setEndE($endA-0);
                        $fight[0]->setEndL($endLw-8);  
                    }
                    if($coup == 4){
                        $fight[0]->setEndE($endA-0);
                        $fight[0]->setEndL($endLw-8);  
                    }
                    if($coup == 5){
                        $fight[0]->setEndE($endA-1);
                        $fight[0]->setEndL($endLw-7);  
                    }
                    if($coup == 6){
                        $fight[0]->setEndE($endA-2);
                        $fight[0]->setEndL($endLw-6);  
                    }
                    if($coup == 7){
                        $fight[0]->setEndE($endA-3);
                        $fight[0]->setEndL($endLw-5);  
                    }
                    if($coup == 8){
                        $fight[0]->setEndE($endA-4);
                        $fight[0]->setEndL($endLw-4);  
                    }
                    if($coup == 9){
                        $fight[0]->setEndE($endA-5);
                        $fight[0]->setEndL($endLw-3);  
                    }
                    if($coup == 0){
                        $fight[0]->setEndE($endA-6);
                        $fight[0]->setEndL($endLw-0);  
                    }    
                }     
            // table -9 / -10
                if($CR== -9 || $CR== -10){
                    if($coup == 1){
                        $fight[0]->setEndE($endA-0);
                        $fight[0]->setEndL(0);  
                    }
                    if($coup == 2){
                        $fight[0]->setEndE($endA-0);
                        $fight[0]->setEndL($endLw-8);  
                    }
                    if($coup == 3){
                        $fight[0]->setEndE($endA-0);
                        $fight[0]->setEndL($endLw-7);  
                    }
                    if($coup == 4){
                        $fight[0]->setEndE($endA-1);
                        $fight[0]->setEndL($endLw-7);  
                    }
                    if($coup == 5){
                        $fight[0]->setEndE($endA-2);
                        $fight[0]->setEndL($endLw-6);  
                    }
                    if($coup == 6){
                        $fight[0]->setEndE($endA-3);
                        $fight[0]->setEndL($endLw-6);  
                    }
                    if($coup == 7){
                        $fight[0]->setEndE($endA-4);
                        $fight[0]->setEndL($endLw-5);  
                    }
                    if($coup == 8){
                        $fight[0]->setEndE($endA-5);
                        $fight[0]->setEndL($endLw-4);  
                    }
                    if($coup == 9){
                        $fight[0]->setEndE($endA-6);
                        $fight[0]->setEndL($endLw-3);  
                    }
                    if($coup == 0){
                        $fight[0]->setEndE($endA-7);
                        $fight[0]->setEndL($endLw-0);  
                    }
                }
            // table -7 / -8
                if($CR== -7 || $CR== -8){
                    if($coup == 1){
                        $fight[0]->setEndE($endA-0);
                        $fight[0]->setEndL($endLw-8);  
                    }
                    if($coup == 2){
                        $fight[0]->setEndE($endA-0);
                        $fight[0]->setEndL($endLw-7);  
                    }
                    if($coup == 3){
                        $fight[0]->setEndE($endA-1);
                        $fight[0]->setEndL($endLw-6);  
                    }
                    if($coup == 4){
                        $fight[0]->setEndE($endA-2);
                        $fight[0]->setEndL($endLw-6);  
                    }
                    if($coup == 5){
                        $fight[0]->setEndE($endA-3);
                        $fight[0]->setEndL($endLw-5);  
                    }
                    if($coup == 6){
                        $fight[0]->setEndE($endA-4);
                        $fight[0]->setEndL($endLw-5);  
                    }
                    if($coup == 7){
                        $fight[0]->setEndE($endA-5);
                        $fight[0]->setEndL($endLw-4);  
                    }
                    if($coup == 8){
                        $fight[0]->setEndE($endA-6);
                        $fight[0]->setEndL($endLw-3);  
                    }
                    if($coup == 9){
                        $fight[0]->setEndE($endA-7);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 0){
                        $fight[0]->setEndE($endA-8);
                        $fight[0]->setEndL($endLw-0);  
                    }
                }
 
            // table -5 / -6
                if($CR== -5 || $CR== -6){
                    if($coup == 1){
                        $fight[0]->setEndE($endA-0);
                        $fight[0]->setEndL($endLw-6);  
                    }
                    if($coup == 2){
                        $fight[0]->setEndE($endA-1);
                        $fight[0]->setEndL($endLw-6);  
                    }
                    if($coup == 3){
                        $fight[0]->setEndE($endA-2);
                        $fight[0]->setEndL($endLw-5);  
                    }
                    if($coup == 4){
                        $fight[0]->setEndE($endA-3);
                        $fight[0]->setEndL($endLw-5);  
                    }
                    if($coup == 5){
                        $fight[0]->setEndE($endA-4);
                        $fight[0]->setEndL($endLw-4);  
                    }
                    if($coup == 6){
                        $fight[0]->setEndE($endA-5);
                        $fight[0]->setEndL($endLw-4);  
                    }
                    if($coup == 7){
                        $fight[0]->setEndE($endA-6);
                        $fight[0]->setEndL($endLw-3);  
                    }
                    if($coup == 8){
                        $fight[0]->setEndE($endA-7);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 9){
                        $fight[0]->setEndE($endA-8);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 0){
                        $fight[0]->setEndE($endA-9);
                        $fight[0]->setEndL($endLw-0);  
                    }
                }
            // table -3 / -4
                if($CR== -3 || $CR== -4){
                    if($coup == 1){
                        $fight[0]->setEndE($endA-1);
                        $fight[0]->setEndL($endLw-6);  
                    }
                    if($coup == 2){
                        $fight[0]->setEndE($endA-2);
                        $fight[0]->setEndL($endLw-5);  
                    }
                    if($coup == 3){
                        $fight[0]->setEndE($endA-3);
                        $fight[0]->setEndL($endLw-5);  
                    }
                    if($coup == 4){
                        $fight[0]->setEndE($endA-4);
                        $fight[0]->setEndL($endLw-4);  
                    }
                    if($coup == 5){
                        $fight[0]->setEndE($endA-5);
                        $fight[0]->setEndL($endLw-4);  
                    }
                    if($coup == 6){
                        $fight[0]->setEndE($endA-6);
                        $fight[0]->setEndL($endLw-3);  
                    }
                    if($coup == 7){
                        $fight[0]->setEndE($endA-7);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 8){
                        $fight[0]->setEndE($endA-8);
                        $fight[0]->setEndL($endLw-1);  
                    }
                    if($coup == 9){
                        $fight[0]->setEndE($endA-9);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 0){
                        $fight[0]->setEndE($endA-10);
                        $fight[0]->setEndL($endLw-0);  
                    }
                }
            // table -1 / -2
                if($CR== -1 || $CR== -2){
                    if($coup == 1){
                        $fight[0]->setEndE($endA-2);
                        $fight[0]->setEndL($endLw-5);  
                    }
                    if($coup == 2){
                        $fight[0]->setEndE($endA-3);
                        $fight[0]->setEndL($endLw-5);  
                    }
                    if($coup == 3){
                        $fight[0]->setEndE($endA-4);
                        $fight[0]->setEndL($endLw-4);  
                    }
                    if($coup == 4){
                        $fight[0]->setEndE($endA-5);
                        $fight[0]->setEndL($endLw-4);  
                    }
                    if($coup == 5){
                        $fight[0]->setEndE($endA-6);
                        $fight[0]->setEndL($endLw-3);  
                    }
                    if($coup == 6){
                        $fight[0]->setEndE($endA-7);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 7){
                        $fight[0]->setEndE($endA-8);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 8){
                        $fight[0]->setEndE($endA-9);
                        $fight[0]->setEndL($endLw-1);  
                    }
                    if($coup == 9){
                        $fight[0]->setEndE($endA-10);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 0){
                        $fight[0]->setEndE($endA-11);
                        $fight[0]->setEndL($endLw-0);  
                    }
                }
            // table 0  
                if($CR==0){
                    if($coup == 1){
                        $fight[0]->setEndE($endA-3);
                        $fight[0]->setEndL($endLw-5);  
                    }
                    if($coup == 2){
                        $fight[0]->setEndE($endA-4);
                        $fight[0]->setEndL($endLw-4);  
                    }
                    if($coup == 3){
                        $fight[0]->setEndE($endA-5);
                        $fight[0]->setEndL($endLw-4);  
                    }
                    if($coup == 4){
                        $fight[0]->setEndE($endA-6);
                        $fight[0]->setEndL($endLw-3);  
                    }
                    if($coup == 5){
                        $fight[0]->setEndE($endA-7);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 6){
                        $fight[0]->setEndE($endA-8);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 7){
                        $fight[0]->setEndE($endA-9);
                        $fight[0]->setEndL($endLw-1);  
                    }
                    if($coup == 8){
                        $fight[0]->setEndE($endA-10);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 9){
                        $fight[0]->setEndE($endA-11);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 0){
                        $fight[0]->setEndE($endA-12);
                        $fight[0]->setEndL($endLw-0);  
                    }
                }
            // table 1 / 2  
                if($CR==1 || $CR==2){
                    if($coup == 1){
                        $fight[0]->setEndE($endA-4);
                        $fight[0]->setEndL($endLw-5);  
                    }
                    if($coup == 2){
                        $fight[0]->setEndE($endA-5);
                        $fight[0]->setEndL($endLw-4);  
                    }
                    if($coup == 3){
                        $fight[0]->setEndE($endA-6);
                        $fight[0]->setEndL($endLw-3);  
                    }
                    if($coup == 4){
                        $fight[0]->setEndE($endA-7);
                        $fight[0]->setEndL($endLw-3);  
                    }
                    if($coup == 5){
                        $fight[0]->setEndE($endA-8);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 6){
                        $fight[0]->setEndE($endA-9);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 7){
                        $fight[0]->setEndE($endA-10);
                        $fight[0]->setEndL($endLw-1);  
                    }
                    if($coup == 8){
                        $fight[0]->setEndE($endA-11);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 9){
                        $fight[0]->setEndE($endA-12);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 0){
                        $fight[0]->setEndE($endA-14);
                        $fight[0]->setEndL($endLw-0);  
                    }
                }
            // table 3 / 4 
                if($CR==3 || $CR==4){
                    if($coup == 1){
                        $fight[0]->setEndE($endA-5);
                        $fight[0]->setEndL($endLw-4);  
                    }
                    if($coup == 2){
                        $fight[0]->setEndE($endA-6);
                        $fight[0]->setEndL($endLw-3);  
                    }
                    if($coup == 3){
                        $fight[0]->setEndE($endA-7);
                        $fight[0]->setEndL($endLw-3);  
                    }
                    if($coup == 4){
                        $fight[0]->setEndE($endA-8);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 5){
                        $fight[0]->setEndE($endA-9);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 6){
                        $fight[0]->setEndE($endA-10);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 7){
                        $fight[0]->setEndE($endA-11);
                        $fight[0]->setEndL($endLw-1);  
                    }
                    if($coup == 8){
                        $fight[0]->setEndE($endA-12);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 9){
                        $fight[0]->setEndE($endA-14);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 0){
                        $fight[0]->setEndE($endA-16);
                        $fight[0]->setEndL($endLw-0);  
                    }
                }
            // table 5 / 6  
                if($CR==5 || $CR==6){
                    if($coup == 1){
                        $fight[0]->setEndE($endA-6);
                        $fight[0]->setEndL($endLw-4);  
                    }
                    if($coup == 2){
                        $fight[0]->setEndE($endA-7);
                        $fight[0]->setEndL($endLw-3);  
                    }
                    if($coup == 3){
                        $fight[0]->setEndE($endA-8);
                        $fight[0]->setEndL($endLw-3);  
                    }
                    if($coup == 4){
                        $fight[0]->setEndE($endA-9);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 5){
                        $fight[0]->setEndE($endA-10);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 6){
                        $fight[0]->setEndE($endA-11);
                        $fight[0]->setEndL($endLw-1);  
                    }
                    if($coup == 7){
                        $fight[0]->setEndE($endA-12);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 8){
                        $fight[0]->setEndE($endA-14);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 9){
                        $fight[0]->setEndE($endA-16);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 0){
                        $fight[0]->setEndE($endA-18);
                        $fight[0]->setEndL($endLw-0);  
                    }
                }
            // table 7 / 8 
                if($CR==7 || $CR==8){
                    if($coup == 1){
                        $fight[0]->setEndE($endA-7);
                        $fight[0]->setEndL($endLw-4);  
                    }
                    if($coup == 2){
                        $fight[0]->setEndE($endA-8);
                        $fight[0]->setEndL($endLw-3);  
                    }
                    if($coup == 3){
                        $fight[0]->setEndE($endA-9);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 4){
                        $fight[0]->setEndE($endA-10);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 5){
                        $fight[0]->setEndE($endA-11);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 6){
                        $fight[0]->setEndE($endA-12);
                        $fight[0]->setEndL($endLw-1);  
                    }
                    if($coup == 7){
                        $fight[0]->setEndE($endA-14);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 8){
                        $fight[0]->setEndE($endA-16);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 9){
                        $fight[0]->setEndE($endA-18);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 0){
                        $fight[0]->setEndE(0);
                        $fight[0]->setEndL($endLw-0);  
                    }
                }
            // table 9 / 10 
                if($CR==9 || $CR==10){
                    if($coup == 1){
                        $fight[0]->setEndE($endA-8);
                        $fight[0]->setEndL($endLw-3);  
                    }
                    if($coup == 2){
                        $fight[0]->setEndE($endA-9);
                        $fight[0]->setEndL($endLw-3);  
                    }
                    if($coup == 3){
                        $fight[0]->setEndE($endA-10);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 4){
                        $fight[0]->setEndE($endA-11);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 5){
                        $fight[0]->setEndE($endA-12);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 6){
                        $fight[0]->setEndE($endA-14);
                        $fight[0]->setEndL($endLw-1);  
                    }
                    if($coup == 7){
                        $fight[0]->setEndE($endA-16);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 8){
                        $fight[0]->setEndE($endA-18);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 9){
                        $fight[0]->setEndE(0);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 0){
                        $fight[0]->setEndE(0);
                        $fight[0]->setEndL($endLw-0);  
                    }
                }
            // table superieur ou égale à 11 
                if($CR > 10){
                    if($coup == 1){
                        $fight[0]->setEndE($endA-9);
                        $fight[0]->setEndL($endLw-3);  
                    }
                    if($coup == 2){
                        $fight[0]->setEndE($endA-10);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 3){
                        $fight[0]->setEndE($endA-11);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 4){
                        $fight[0]->setEndE($endA-12);
                        $fight[0]->setEndL($endLw-2);  
                    }
                    if($coup == 5){
                        $fight[0]->setEndE($endA-14);
                        $fight[0]->setEndL($endLw-1);  
                    }
                    if($coup == 6){
                        $fight[0]->setEndE($endA-16);
                        $fight[0]->setEndL($endLw-1);  
                    }
                    if($coup == 7){
                        $fight[0]->setEndE($endA-18);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 8){
                        $fight[0]->setEndE(0);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 9){
                        $fight[0]->setEndE(0);
                        $fight[0]->setEndL($endLw-0);  
                    }
                    if($coup == 0){
                        $fight[0]->setEndE(0);
                        $fight[0]->setEndL($endLw-0);  
                    }
                }
                return $fight;
    }


}