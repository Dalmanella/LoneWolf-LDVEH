<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Hero;
use App\Repository\BackpackRepository;

class Armure {

    public function __construct(
        EntityManagerInterface $entityManager,
        BackpackRepository $backpackRep,
        Hero $hero
        )
    {
        $this->entityManager = $entityManager;
        $this->backpackRep = $backpackRep; 
        $this->hero = $hero;
    }

    public function armure($heroId){

        $hero= $this->entityManager->getRepository(Hero::class) -> findBy(['id'=>$heroId]);
        $inventaire= $this->backpackRep-> findBy(['heroId'=>$hero]);

        $end=$this->hero->getEndurance();

        $protectionH=$inventaire[0]->getObjetId(23);
        $protectionC=$inventaire[0]->getObjetId(24);

        if($protectionH){
            
            $end= $this->hero->setEndurance($end+2);

            $this->entityManager->persist($end);
            $this->entityManager->flush();
        }
    }

   
}