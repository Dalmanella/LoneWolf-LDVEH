<?php

namespace App\Controller;

use App\Entity\Hero;
use App\Service\RollDice;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\KaiDisRepository;
use App\Entity\KaiDis;
use App\Form\NewAdventureType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Utilisateur;


class NewAdventureController extends AbstractController
{
    private $rollDice;
    private $requestStack;
    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack, RollDice $rollDice)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->rollDice = $rollDice;
    }
        
   

    /**
    * @Route("/nouvelle_aventure", name="nouvelle")
    */
    public function viewNewAdventure(Request $request): Response
    {
        //liste des disciplines
        $disciplines= $this->entityManager->getRepository(KaiDis::class)->findAll();

        //formulaire du nouveau hero
        $hero= new Hero();
        $form = $this->createForm(NewAdventureType::class, $hero);
  
        $form ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {

            $heroDatas  = $form->getData();

                $hero->setUserId( $this->getUser() );
            // dd($hero);

                $this->entityManager->persist($heroDatas);
                $this->entityManager->flush();

                $this->addFlash('succes','héro ajouté.');
                return $this->redirectToRoute('page_intro'); 
        }
        return $this->render('newAdventure/newAdventure.html.twig', ['disciplines'=>$disciplines,'heroForm' => $form->createView()] );
    
    }

    
}