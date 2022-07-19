<?php

namespace App\Controller;

use App\Entity\Hero;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;



class GestionController extends AbstractController
{
    private $entityManager;
    private $requestStack;
    

    public function __construct(
        EntityManagerInterface $entityManager, 
        RequestStack $requestStack, 
        UtilisateurRepository $utilisateurRep
        
        )
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;     
        $this->utilisateurRep =$utilisateurRep;
        
    }

    /**
    * @Route("/gestion", name="gestion")
    */
    public function viewGestion()
    {
        $utilisateurs= $this->utilisateurRep->findAll();
        
        
        return $this->render('gestion/gestion.html.twig',[
            'utilisateurs'=>$utilisateurs
        ]);
    }

    /**
     * @Route("/userDel/{userId}", name="userDel")
     */
    public function delUser($userId)
    {
        $user = $this->entityManager->getRepository(Utilisateur::class) -> find($userId);
        
        if ($user){
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            
        };

        $this->addFlash('success','Utilisateur supprimé.');  //permet d'afficher des alertes
        return $this->redirectToRoute('gestion');

    }
    
}