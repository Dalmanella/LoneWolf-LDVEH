<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Hero;

class HeroController extends AbstractController
{
    public function __construct(
        EntityManagerInterface $entityManager, 
        RequestStack $requestStack
    )
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/hero/delete/{heroId}", name="heroDel")
     */
    public function delHero($heroId)
    {

        $hero = $this->entityManager->getRepository(Hero::class) -> find($heroId);
        
        if ($hero){
            $this->entityManager->remove($hero);
            $this->entityManager->flush();
            
        };

        $this->addFlash('success','Hero deleted.');  //permet d'afficher des alertes
        return $this->redirectToRoute('continue');

    }

    /**
     * @Route("/hero/addBackpack/{p}/{heroId}", name="Add_Backpack")
     */
        public function addBackpack(
            $p, 
            $heroId
        )
        {
            $page= strval('page_'.$p);

            $hero= $this->entityManager->getRepository(Hero::class) -> findBy(['id'=>$heroId]);
            
            $backpack=$hero[0]->setBackpack(1);
            
            $this->entityManager->persist($backpack);
            $this->entityManager->flush();


            $this->addFlash('succes','backpack added.');  //permet d'afficher des alertes
            return $this->redirectToRoute($page, ['heroId'=>$heroId]);
        }

    /**
     * @Route("/hero/delBackpack/{p}/{heroId}", name="del_Backpack")
     */
        public function delBackpack(
            $p, 
            $heroId
        )
        {
            $page= strval('page_'.$p);

            $hero= $this->entityManager->getRepository(Hero::class) -> findBy(['id'=>$heroId]);
            
            $backpack=$hero[0]->setBackpack(0);
            
            $this->entityManager->persist($backpack);
            $this->entityManager->flush();


            $this->addFlash('succes','backpack added.');  //permet d'afficher des alertes
            return $this->redirectToRoute($page, ['heroId'=>$heroId]);
        }

    /**
     * @Route("/hero/addGold/{p}/{heroId}/{coins}", name="add_gold")
     */
    public function addGold(
        $p, 
        $heroId,
        $coins
    )
    {
        $page= strval('page_'.$p);

        $hero= $this->entityManager->getRepository(Hero::class) -> findBy(['id'=>$heroId]);
        
        $gold=$hero[0]->getGold();
        $hero[0]->setGold($gold+$coins);

        if($hero[0]->getGold()>50){
            $hero[0]->setGold(50);
        };
        $hero[0]->setGoldTake(true);
        $this->entityManager->persist($hero[0]);
        $this->entityManager->flush();
        

        $this->addFlash('succes','coins added.');  //permet d'afficher des alertes
        return $this->redirectToRoute($page, ['heroId'=>$heroId]);
    }

}