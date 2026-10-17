<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\CountryFormType;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class SettingsController extends AbstractController
{
    #[Route(path: '/settings', name: 'app_settings', methods: ['GET'])]
    public function index(): Response
    {
        return $this->redirectToRoute('app_settings_users');
    }

    #[Route(path: '/settings/classes', name: 'app_settings_classes', methods: ['GET'])]
    public function classes(): Response
    {
        return $this->render('settings/classes.html.twig');
    }

    #[Route(path: '/settings/users', name: 'app_settings_users', methods: ['GET', 'POST'])]
    public function users(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new LogicException('Expected an authenticated User.');
        }

        $countryForm = $this->createForm(CountryFormType::class, $user);
        $countryForm->handleRequest($request);

        if ($countryForm->isSubmitted() && $countryForm->isValid()) {
            $entityManager->flush();

            $this->addFlash('country_success', 'Your country has been saved.');

            return $this->redirectToRoute('app_settings_users');
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();
            if (!\is_string($newPassword)) {
                throw new LogicException('Expected newPassword field to submit a string.');
            }

            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $entityManager->flush();

            $this->addFlash('success', 'Your password has been changed.');

            return $this->redirectToRoute('app_settings_users');
        }

        return $this->render('settings/users.html.twig', [
            'countryForm' => $countryForm,
            'changePasswordForm' => $form,
        ]);
    }
}
