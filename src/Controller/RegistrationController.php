<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\SignupGate;
use App\Security\SignupState;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly SignupGate $signupGate,
    ) {
    }

    #[Route(path: '/signup', name: 'app_signup', methods: ['GET', 'POST'])]
    public function signup(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        Security $security,
    ): Response {
        if ($this->signupGate->state() === SignupState::Disabled) {
            return $this->render('security/signup_unavailable.html.twig', [
                'message' => 'Signup is currently closed.',
            ], new Response(status: 404));
        }

        $invite = $request->query->get('invite');
        if (!$this->signupGate->allows(\is_string($invite) ? $invite : null)) {
            return $this->render('security/signup_unavailable.html.twig', [
                'message' => 'A valid invite link is required to sign up.',
            ], new Response(status: 403));
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if (!\is_string($plainPassword)) {
                throw new LogicException('Expected plainPassword field to submit a string.');
            }

            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            $security->login($user, 'form_login');

            return new RedirectResponse($this->generateUrl('app_home'));
        }

        return $this->render('security/signup.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
