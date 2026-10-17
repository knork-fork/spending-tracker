<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SyncController extends AbstractController
{
    #[Route(path: '/sync', name: 'app_sync', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('sync/index.html.twig');
    }

    #[Route(path: '/sync/bank', name: 'app_sync_bank', methods: ['GET'])]
    public function bank(): Response
    {
        return $this->render('sync/bank.html.twig');
    }
}
