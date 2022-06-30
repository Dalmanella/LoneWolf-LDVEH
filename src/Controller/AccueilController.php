<?php

namespace App\Controller;

use Doctrine\ORM\Mapping\Id;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\RequestStack;


class AccueilController extends AbstractController
{
    private $requestStack;
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        
    }

    /**
     * @Route("/", name="accueil")
     */
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        $session = $this->requestStack->getSession();
       
        // if($session->has('name')){
        //     dd($session);
        //     session_destroy();
        // }
       

        // get the login error if there is one
         $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
         $lastUsername = $authenticationUtils->getLastUsername();

         $datas=[
            'last_username' => $lastUsername,
            'error'         => $error,            
        ];

        
            return $this->render('accueil/index.html.twig', $datas);
        
    }
}
