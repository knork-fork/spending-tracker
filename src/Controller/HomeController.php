<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dashboard\ChartGeometry;
use App\Dashboard\FakeSpendingData;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(
        private readonly FakeSpendingData $spendingData,
        private readonly ChartGeometry $chartGeometry,
    ) {
    }

    #[Route(path: '/', name: 'app_home', methods: ['GET'])]
    public function __invoke(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User || $user->getCountry() === null) {
            return $this->render('home/choose_country.html.twig');
        }

        $breakdown = $this->spendingData->lastMonthBreakdown();

        return $this->render('home/index.html.twig', [
            'chart' => $this->chartGeometry->lineChart($this->spendingData->monthlyTotals()),
            'chartWidth' => ChartGeometry::WIDTH,
            'chartHeight' => ChartGeometry::HEIGHT,
            'chartPadLeft' => ChartGeometry::PAD_LEFT,
            'chartPadRight' => ChartGeometry::PAD_RIGHT,
            'chartBaseline' => ChartGeometry::HEIGHT - ChartGeometry::PAD_BOTTOM,
            'donut' => $this->chartGeometry->donut($breakdown),
            'donutRadius' => ChartGeometry::DONUT_RADIUS,
            'breakdown' => $breakdown,
            'breakdownTotal' => array_sum(array_column($breakdown, 'amount')),
        ]);
    }
}
