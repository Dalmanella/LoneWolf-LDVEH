<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;



class AproposController extends AbstractController
{

    /**
    * @Route("/apropos", name="A_propos")
    */
    public function viewApropos()
    {
        
        
        
        return $this->render('apropos/apropos.html.twig');
    }

    
}