<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EmailVerificationType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\Authenticator;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'resendVerificationEmailForm' => $error instanceof CustomUserMessageAccountStatusException
                ? $this->createForm(EmailVerificationType::class, null, ['email' => $lastUsername, 'action' => $this->generateUrl('app_verification_email_resend'), 'method' => 'POST'])->createView()
                : null,
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): never
    {
        throw new LogicException();
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'], condition: '%app.registration.enabled% == true')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        EmailVerifier $emailVerifier,
        UserAuthenticatorInterface $userAuthenticator,
        Authenticator $authenticator
    ): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            if ($this->getParameter('app.registration.email_verification.enabled')) {
                $this->sendVerificationEmail($emailVerifier, $user);

                $this->addFlash('success', 'email verification was send');

                return $this->redirectToRoute('app_login');
            }

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/verification/email/resend', name: 'app_verification_email_resend', methods: ['POST'], condition: '%app.registration.email_verification.enabled% == true')]
    public function resendEmailVerificationEmail(
        Request $request,
        UserRepository $userRepository,
        EmailVerifier $emailVerifier
    ): Response
    {
        $form = $this->createForm(EmailVerificationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findOneBy(['email' => $form->get('email')->getData()]);

            if ($user) {
                $this->sendVerificationEmail($emailVerifier, $user);
            }

            $this->addFlash('success', 'email verification was send');
        }

        return $this->redirectToRoute('app_login');
    }

    #[Route('/verification/email', name: 'app_verification_email', methods: ['GET'], condition: '%app.registration.email_verification.enabled% == true')]
    public function verifyUserEmail(
        Request $request,
        UserRepository $userRepository,
        EmailVerifier $emailVerifier,
        UserAuthenticatorInterface $userAuthenticator,
        Authenticator $authenticator
    ): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        try {
            $emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verification_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'your email address has been verified.');

        return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
        );
    }

    private function sendVerificationEmail(EmailVerifier $emailVerifier, User $user): void
    {
        $emailVerifier->sendEmailConfirmation('app_verification_email', $user,
            (new TemplatedEmail())
                ->from(new Address('rss-wrapper@e-hess.com', 'rss-wrapper@e-hess.com'))
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('security/confirmation_email.html.twig')
        );
    }
}