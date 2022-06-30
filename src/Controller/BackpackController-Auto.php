<?php

namespace App\Controller;

use App\Entity\Backpack;
use App\Form\BackpackType;
use App\Repository\BackpackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backpack")
 */
class BackpackController extends AbstractController
{
    /**
     * @Route("/", name="app_backpack_index", methods={"GET"})
     */
    public function index(BackpackRepository $backpackRepository): Response
    {
        return $this->render('backpack/index.html.twig', [
            'backpacks' => $backpackRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_backpack_new", methods={"GET", "POST"})
     */
    public function new(Request $request, BackpackRepository $backpackRepository): Response
    {
        $backpack = new Backpack();
        $form = $this->createForm(BackpackType::class, $backpack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $backpackRepository->add($backpack, true);

            return $this->redirectToRoute('app_backpack_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backpack/new.html.twig', [
            'backpack' => $backpack,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_backpack_show", methods={"GET"})
     */
    public function show(Backpack $backpack): Response
    {
        return $this->render('backpack/show.html.twig', [
            'backpack' => $backpack,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_backpack_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Backpack $backpack, BackpackRepository $backpackRepository): Response
    {
        $form = $this->createForm(BackpackType::class, $backpack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $backpackRepository->add($backpack, true);

            return $this->redirectToRoute('app_backpack_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('backpack/edit.html.twig', [
            'backpack' => $backpack,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_backpack_delete", methods={"POST"})
     */
    public function delete(Request $request, Backpack $backpack, BackpackRepository $backpackRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$backpack->getId(), $request->request->get('_token'))) {
            $backpackRepository->remove($backpack, true);
        }

        return $this->redirectToRoute('app_backpack_index', [], Response::HTTP_SEE_OTHER);
    }
}
