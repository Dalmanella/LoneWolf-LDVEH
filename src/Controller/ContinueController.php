<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Hero;


class ContinueController extends AbstractController
{
    private $rollDice;
    private $requestStack;
    public function __construct(
        EntityManagerInterface $entityManager, 
        RequestStack $requestStack
    )
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;

    }
        
   

    /**
    * @Route("/continue", name="continue")
    */
    public function viewContinue() 
    {
        // recuperer l'Id de l'utilisateur connecté:
            /** @var Utilisateur $user */
            
            $user= $this->getUser();
            if(!empty($user))
            {
                $userId = $user->getId();
            }

            //tous les heros du $userId
            $heroes= $this->entityManager->getRepository(Hero::class)-> findBy(['userId'=>$userId]);
            
            $length= count($heroes);
            
        
        
        return $this->render('continue/continue.html.twig',['heroes'=>$heroes]);
    }

    
}