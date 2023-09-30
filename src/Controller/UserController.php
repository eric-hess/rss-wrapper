<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\LockedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_list', methods: ['GET'])]
    public function list(UserRepository $userRepository): Response
    {
        return $this->render('user/list.html.twig', [
            'users' => $this->isGranted('ROLE_ADMIN') ? $userRepository->findAll() : [$this->getUser()],
        ]);
    }

    #[Route('/create', name: 'app_user_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
        EmailVerifier $emailVerifier
    ): Response
    {
        if(!$this->isGranted('ROLE_ADMIN')) {
            throw new LockedHttpException();
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'show_roles' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();

            if ($this->getParameter('app.registration.email_verification.enabled')) {
                $emailVerifier->sendVerificationEmail($user);

                $this->addFlash('success', 'email verification was send');
            }

            $this->addFlash('success', 'user successfully created');

            return $this->redirectToRoute('app_user_list');
        }

        return $this->render('user/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_user_delete', methods: ['GET'])]
    public function delete(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response
    {
        if($this->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw new LockedHttpException();
        }

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->get('token'))) {
            $entityManager->remove($user);
            $entityManager->flush();

            if ($this->getUser()->getUserIdentifier() === $user->getUserIdentifier()) {
                $security->logout(false);

                return $this->redirectToRoute('app_login');
            }

            $this->addFlash('success', 'user successfully deleted');
        }

        return $this->redirectToRoute('app_user_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_user_show', methods: 'GET')]
    public function show(User $user): Response
    {
        if ($this->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw new LockedHttpException();
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        EmailVerifier $emailVerifier,
        Security $security
    ): Response
    {
        if($user !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw new LockedHttpException();
        }

        $form = $this->createForm(UserType::class, $user, [
            'show_confirm_current_password' => !$this->isGranted('ROLE_ADMIN'),
            'email_required' => false,
            'plain_password_required' => false,
            'show_roles' => $this->isGranted('ROLE_ADMIN'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('ROLE_ADMIN') && !$userPasswordHasher->isPasswordValid($user, $form->get('currentPassword')->getData())) {
                $this->addFlash('error', 'wrong password');
            } else {
                $sendVerificationEmail = false;
                $entityManager->getUnitOfWork()->computeChangeSet($entityManager->getClassMetadata(User::class), $user);

                if (array_key_exists('email', $entityManager->getUnitOfWork()->getEntityChangeSet($user)) && $this->getParameter('app.registration.email_verification.enabled')) {
                    $user->setIsVerified(0);
                    $sendVerificationEmail = true;
                }

                if (!empty($form->get('plainPassword')->getData())) {
                    $user->setPassword(
                        $userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData())
                    );
                }

                $entityManager->flush();

                if ($sendVerificationEmail) {
                    $emailVerifier->sendVerificationEmail($user);

                    if ($user === $this->getUser()) {
                        $logoutResponse = $security->logout(false);

                        $this->addFlash('success', 'email verification was send');

                        return $logoutResponse;
                    }
                }

                return $this->redirectToRoute('app_user_list', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}