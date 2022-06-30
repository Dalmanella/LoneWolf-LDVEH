<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Backpack;
use App\Repository\BackpackRepository;
use App\Service\Inventory;
use App\Entity\Objet;
use App\Entity\Hero;

class BackpackController extends AbstractController
{
    public function __construct(
        EntityManagerInterface $entityManager, 
        RequestStack $requestStack,
        Inventory $inventory,
        BackpackRepository $backpackRep
        
    )
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->inventory = $inventory;
        $this->backpackRep = $backpackRep;
        
    }

/**
     * @Route("/backpack/delete/{itemId}/{p}/{heroId}", name="objDel")
     */
        public function delObject(
            int $itemId, 
            $p, 
            $heroId
            
        )
        {
        $page= strval('page_'.$p);
        $eat=0;
            
            $hero= $this->entityManager->getRepository(Hero::class) -> findBy(['id'=>$heroId]);
            $inventaire= $this->backpackRep-> findBy(['heroId'=>$hero]);
            $backpack=$inventaire[0];
            
            $items = $this->inventory->inventory($heroId);
            
            $itemsLength=count($items);
            
            for($i=0;$i<$itemsLength;$i++){

                $item=$this->entityManager->find(Objet::class, $items[$i]->getId());
            
                if($item->getId() == $itemId){
                    $backpack -> removeObjetId($item);        
                    
                }else{
                    $backpack -> addObjetId($item);
                    
                }
            }
            
                $this->entityManager->persist($backpack);
                $this->entityManager->flush();

                $eat+=1; 
            
            $this->addFlash('succes','item deleted.');  //permet d'afficher des alertes
            return $this->redirectToRoute($page, ['heroId'=>$heroId, 'eat'=>$eat]);

        }

/**
* @Route("/backpack/add/{itemId}/{p}/{heroId}", name="objAdd")
*/
        public function addObject(
            int $itemId, 
            $p, 
            $heroId){
            $page= strval('page_'.$p);

            $hero= $this->entityManager->getRepository(Hero::class) -> findBy(['id'=>$heroId]);
            $inventaire= $this->backpackRep-> findBy(['heroId'=>$hero]);
            $backpack=$inventaire[0];

            // $items = $this->inventory->inventory($heroId);

            $item=$this->entityManager->find(Objet::class, $itemId);
            
            $backpack -> addObjetId($item);
            // dd($item);
            $this->entityManager->persist($backpack);
            $this->entityManager->flush();

            if($p == 'intro'){
                $hero[0]->setIsItem(1);
                $this->entityManager->persist($hero[0]);
                $this->entityManager->flush();
            }

            $this->addFlash('succes','item added.');  //permet d'afficher des alertes
            
            return $this->redirectToRoute($page, ['heroId'=>$heroId]);
            
        }

/**
 * @Route("/backpack/delAll/{p}/{heroId}", name="objDelAll")
 */
    public function deleteAll(
        $p, 
        $heroId
    )
    {
       $page= strval('page_'.$p);
        
        $hero= $this->entityManager->getRepository(Hero::class) -> findBy(['id'=>$heroId]);
        $inventaire= $this->backpackRep-> findBy(['heroId'=>$hero]);
        $backpack=$inventaire[0];
        
        $items = $this->inventory->inventory($heroId);
        
        $itemsLength=count($items);
        
        for($i=0;$i<$itemsLength;$i++){

            $item=$this->entityManager->find(Objet::class, $items[$i]->getId());
        
            
            $backpack -> removeObjetId($item);        
                
            
        }
        
            $this->entityManager->persist($backpack);
            $this->entityManager->flush();
        
        $this->addFlash('succes','item deleted.');  //permet d'afficher des alertes
        return $this->redirectToRoute($page, ['heroId'=>$heroId]);

    }

/**
     * @Route("/backpack/drink/{itemId}/{p}/{heroId}/{ennemyId}/{ennemyName}/{CS}/{CR}/{fightId}/{pO}", name="objDrink")
     */
    public function drinkPotionS(
        int $itemId, 
        $p, 
        $heroId,
        $ennemyId, 
        $ennemyName, 
        $CS, 
        $CR, 
        $fightId,
        $pO        
    )
    {
       $page= strval('page_'.$p);
       $eat=0;
        
        $hero= $this->entityManager->getRepository(Hero::class) -> findBy(['id'=>$heroId]);
        $inventaire= $this->backpackRep-> findBy(['heroId'=>$hero]);
        $backpack=$inventaire[0];
        
        $items = $this->inventory->inventory($heroId);
        
        $itemsLength=count($items);
        
        for($i=0;$i<$itemsLength;$i++){

            $item=$this->entityManager->find(Objet::class, $items[$i]->getId());
        
            if($item->getId() == $itemId){
                $backpack -> removeObjetId($item);        
                
            }else{
                $backpack -> addObjetId($item);
                
            }
        }
        
            $this->entityManager->persist($backpack);
            $this->entityManager->flush();

            $eat+=1; 
        
        $this->addFlash('succes','item deleted.');  //permet d'afficher des alertes
        return $this->redirectToRoute($page, [
            'heroId'=>$heroId, 
            "ennemyId"=>$ennemyId,
            'ennemyName'=>$ennemyName, 
            "CS"=>$CS,"CR"=>$CR,
            'fightId'=>$fightId,
            'p'=>$pO
             
        ]);

    }
/**
     * @Route("/backpack/eat/{itemId}/{p}/{heroId}", name="objEat")
     */
        public function eat(
            int $itemId, 
            $p, 
            $heroId
            
        )
        {
        $page= strval('page_'.$p);
        $eat=0;
            
            $hero= $this->entityManager->getRepository(Hero::class) -> findBy(['id'=>$heroId]);
            $inventaire= $this->backpackRep-> findBy(['heroId'=>$hero]);
            $backpack=$inventaire[0];
            
            $items = $this->inventory->inventory($heroId);
            
            $itemsLength=count($items);
            
            for($i=0;$i<$itemsLength;$i++){

                $item=$this->entityManager->find(Objet::class, $items[$i]->getId());
                $eatId = $item->getId();
                if($eatId == $itemId){
                    $backpack -> removeObjetId($item);   
                    
                        if($eatId == 25){
                            $hero[0] -> setEndurance($hero[0]->getEndurance()+4);
                            if($hero[0]->getEndurance()>$hero[0]->getEndMax()){
                                $hero[0] -> setEndurance($hero[0]->getEndMax());
                            }
                        };
                        if($eatId == 12){
                            $hero[0] -> setEndurance($hero[0]->getEndurance()+3);
                            if($hero[0]->getEndurance()>$hero[0]->getEndMax()){
                                $hero[0] -> setEndurance($hero[0]->getEndMax());
                            }
                        };
                        $this->entityManager->persist($hero[0]);

                        $this->entityManager->flush();
                }else{
                    $backpack -> addObjetId($item);
                    
                };
            }
            
                $this->entityManager->persist($backpack);
                $this->entityManager->flush();

                $eat+=1; 
            
            $this->addFlash('succes','item deleted.');  //permet d'afficher des alertes
            return $this->redirectToRoute($page, ['heroId'=>$heroId, 'eat'=>$eat]);

        }

        /**
    * @Route("/backpack/addMeals/{p}/{heroId}", name="addMeals")
    */
    public function addMeals(
        
        $p, 
        $heroId){
        $page= strval('page_'.$p);

        $hero= $this->entityManager->getRepository(Hero::class) -> findBy(['id'=>$heroId]);
        $inventaire= $this->backpackRep-> findBy(['heroId'=>$hero]);
        $backpack=$inventaire[0];

        $item1=$this->entityManager->find(Objet::class, 30);
        $item2=$this->entityManager->find(Objet::class, 31);
        
        $backpack -> addObjetId($item1);
        $backpack -> addObjetId($item2);
        
        $this->entityManager->persist($backpack);
        $this->entityManager->flush();

        $hero[0]->setIsItem(1);
        $this->entityManager->persist($hero[0]);
        $this->entityManager->flush();
        

        $this->addFlash('succes','item added.');  //permet d'afficher des alertes
        
        return $this->redirectToRoute($page, ['heroId'=>$heroId]);
        
    }

    /**
    * @Route("/backpack/ex/{itemId}/{p}/{heroId}", name="objEx")
    */
        public function ExObject(
            int $itemId, 
            $p, 
            $heroId){
            $page= strval('page_'.$p);

            $hero= $this->entityManager->getRepository(Hero::class) -> findBy(['id'=>$heroId]);
            $inventaire= $this->backpackRep-> findBy(['heroId'=>$hero]);
            $backpack=$inventaire[0];

            // $items = $this->inventory->inventory($heroId);

            $item=$this->entityManager->find(Objet::class, $itemId);
            $wh=$this->entityManager->find(Objet::class, 17);

            $backpack -> removeObjetId($item);
            $backpack -> addObjetId($wh);
            // dd($item);
            $this->entityManager->persist($backpack);
            $this->entityManager->flush();

            if($p == 'intro'){
                $hero[0]->setIsItem(1);
                $this->entityManager->persist($hero[0]);
                $this->entityManager->flush();
            }

            $this->addFlash('succes','item added.');  //permet d'afficher des alertes
            
            return $this->redirectToRoute($page, ['heroId'=>$heroId]);
            
        }

}