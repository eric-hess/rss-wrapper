<?php

namespace App\Controller;

use App\Entity\Wrapper;
use App\Form\WrapperType;
use App\Repository\WrapperRepository;
use App\Service\WrapperService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\LockedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/wrapper')]
class WrapperController extends AbstractController
{
    #[Route('/', name: 'app_wrapper_index', methods: ['GET'])]
    public function index(WrapperRepository $wrapperRepository): Response
    {
        return $this->render('wrapper/index.html.twig', [
            'wrappers' => $wrapperRepository->findBy(['user' => $this->getUser()]),
        ]);
    }

    #[Route('/new', name: 'app_wrapper_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $wrapper = new Wrapper();
        $form = $this->createForm(WrapperType::class, $wrapper);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $wrapper->setUser($this->getUser());
            $entityManager->persist($wrapper);
            $entityManager->flush();

            return $this->redirectToRoute('app_wrapper_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('wrapper/new.html.twig', [
            'wrapper' => $wrapper,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_wrapper_show', methods: ['GET'], format: 'xml')]
    public function show(Wrapper $wrapper, WrapperService $wrapperService): Response
    {
        return new Response(
            $wrapperService->wrap($wrapper),
            Response::HTTP_OK
        );
    }

    #[Route('/{id}/edit', name: 'app_wrapper_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Wrapper $wrapper, EntityManagerInterface $entityManager): Response
    {
        if ($wrapper->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw new LockedHttpException();
        }

        $form = $this->createForm(WrapperType::class, $wrapper);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_wrapper_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('wrapper/edit.html.twig', [
            'wrapper' => $wrapper,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_wrapper_delete', methods: ['GET'])]
    public function delete(Request $request, Wrapper $wrapper, EntityManagerInterface $entityManager): Response
    {
        if ($wrapper->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw new NotFoundHttpException();
        }

        if ($this->isCsrfTokenValid('delete'.$wrapper->getId(), $request->get('token'))) {
            $entityManager->remove($wrapper);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_wrapper_index', [], Response::HTTP_SEE_OTHER);
    }
}
