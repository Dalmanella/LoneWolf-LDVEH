<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UtilisateurRepository;
use Doctrine\Persistence\ManagerRegistry;


class MusicController extends AbstractController
{
    private $requestStack;
    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        
    }

    /**
    * @Route("/music", name="music")
    */
        public function viewMusic() : Response
        {        
            return $this->render('musique/musique.html.twig', []);
        }

   
}