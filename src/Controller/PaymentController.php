<?php

namespace App\Controller;

use App\Service\PaymentProcessor;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    private PaymentProcessor $paymentProcessor;

    public function __construct(PaymentProcessor $paymentProcessor)
    {
        $this->paymentProcessor = $paymentProcessor;
    }

    /**
     * @Route("/app/example/{system}", name="payment_process", methods={"GET"})
     */
    public function processPayment(string $system, float $amount): JsonResponse
    {
        if (!in_array($system, ['aci', 'shift4'])) {
            return new JsonResponse(['error' => 'Invalid payment system'], 400);
        }

        try {
            $paymentResponse = $this->paymentProcessor->processPayment($system, $amount);
            return $this->json($paymentResponse);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Failed to process payment', 'details' => $e->getMessage()], 500);
        }
    }
}
