<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Hero;
use App\Repository\BackpackRepository;

class Inventory {

    public function __construct(
        EntityManagerInterface $entityManager,
        BackpackRepository $backpackRep
        
        )
    {
        $this->entityManager = $entityManager;
        $this->backpackRep = $backpackRep; 
    }
    public function inventory($heroId) : array
    {
        $hero= $this->entityManager->getRepository(Hero::class) -> findBy(['id'=>$heroId]);
        $inventaire= $this->backpackRep-> findBy(['heroId'=>$hero]);
        
        $items = $inventaire[0]->getObjetId()->getValues();  
        return $items ;
        
    }

}