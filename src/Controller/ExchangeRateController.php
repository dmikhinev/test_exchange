<?php

namespace App\Controller;

use App\Service\RateManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ExchangeRateController extends AbstractController
{
    /**
     * @Route("/exchange/rate/{currencyFrom}/{currencyTo}/{value}", name="exchange_rate")
     */
    public function index($currencyFrom, $currencyTo, $value, RateManager $manager)
    {
        try {
            $convert = $manager->convert($value, $currencyFrom, $currencyTo);

            return new JsonResponse(['result' => $convert]);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => true, 'messages' => [$e->getMessage()]], 500);
        }
    }
}
