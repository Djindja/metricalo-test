<?php

namespace App\Controller;

use App\Service\PaymentProcessor;
use Exception;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class PaymentController extends AbstractController
{
    private PaymentProcessor $paymentProcessor;

    public function __construct(PaymentProcessor $paymentProcessor)
    {
        $this->paymentProcessor = $paymentProcessor;
    }

    /**
     * @Route("/app/example/{system}", name="payment_process", methods={"GET"})
     *
     * @OA\Get(
     *     path="/app/example/{system}",
     *     summary="Process a payment",
     *     @OA\Parameter(
     *         name="system",
     *         in="path",
     *         required=true,
     *         description="The payment system to use (e.g., 'aci', 'shift4')",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="amount",
     *         in="query",
     *         required=true,
     *         description="The amount to process",
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment processed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="currency", type="string"),
     *             @OA\Property(property="id", type="string"),
     *             @OA\Property(property="source", type="string"),
     *             @OA\Property(property="date", type="string"),
     *             @OA\Property(property="expiryYear", type="integer"),
     *             @OA\Property(property="expiryMonth", type="integer"),
     *             @OA\Property(property="amount", type="number", format="float"),
     *             @OA\Property(property="cvv", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid payment system",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to process payment",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="details", type="string")
     *         )
     *     )
     * )
     *
     * @param string $system The payment system to use (e.g., 'aci', 'shift4')
     * @param float $amount The amount to process
     *
     * @return JsonResponse The JSON response containing the payment details or an error message
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
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
