<?php

namespace App\Controller;

use App\Repository\CombatEndRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class TableManagerController extends AbstractController
{
    /**
     * @Route("/combatEnd/clear", name="clearTable")
     */
    public function clearCombatInfo(CombatEndRepository $clearcombat)
    {
        $clearcombat->clearTable();
        $this->addFlash('success','table vidée.');

        // return new Response('table clear success');

        return $this->redirectToRoute('gestion');
    }
}