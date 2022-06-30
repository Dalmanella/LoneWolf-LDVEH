<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UtilisateurRepository;
use App\Entity\Utilisateur;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\HttpFoundation\Request;

class AppliController extends AbstractController
{
    private $requestStack;
    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        
    }

    /**
    * @Route("/appli", name="appli")
    */
    public function viewAppli(ManagerRegistry $doctrine, UtilisateurRepository $UtilisateurRepository) : Response
    {        
        // recuperer l'Id de l'utilisateur connecté:
            /** @var Utilisateur $user */
            
            $user= $this->getUser();
            if(!empty($user))
            {
                $userId = $user->getId();
            }
     
       
        return $this->render('appli/appli.html.twig', []);
    }

   
}