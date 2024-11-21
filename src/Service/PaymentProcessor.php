<?php

namespace App\Service;

use App\DTO\PaymentResponse;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PaymentProcessor
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function processPayment(string $system, float $amount): PaymentResponse
    {
        if ($system === 'aci') {
            return $this->processAciPayment($amount);
        } elseif ($system === 'shift4') {
            return $this->processShift4Payment($amount);
        }

        throw new InvalidArgumentException("Unsupported payment system: $system");
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function processShift4Payment(float $amount): PaymentResponse
    {
        $url = 'https://api.shift4.com/v1/charges';
        $authKey = $_ENV['SHIFT4_PRIVATE_KEY'] ?? null;

        if (!$authKey) {
            throw new RuntimeException('SHIFT4_PRIVATE_KEY environment variable is not set.');
        }

        $response = $this->client->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $authKey,
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'amount' => $amount * 100,
                'currency' => 'USD',
                'source' => [
                    'type' => 'card',
                    'number' => '4242000000000083',
                    'expMonth' => 12,
                    'expYear' => 2025,
                    'cvc' => '123',
                ],
                'capture' => true,
                'description' => 'Test charge',
                'metadata' => [
                    'order_id' => '6735'
                ]
            ],
        ]);

        $data = $response->toArray();

        return new PaymentResponse(
            $data['currency'],
            $data['id'],
            $data['source']['number'],
            (new \DateTime())->format('Y-m-d H:i:s'),
            2025,
            12,
            $amount,
            '123'
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function processAciPayment(float $amount): PaymentResponse
    {
        $url = 'https://test.oppwa.com/v1/payments';
        $authKey = $_ENV['ACI_PRIVATE_KEY'] ?? null;;
        $entityId = '8ac7a4c8767432d501767474c18e0222';

        if (!$authKey) {
            throw new RuntimeException('ACI_PRIVATE_KEY environment variable is not set.');
        }


        $response = $this->client->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $authKey,
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'entityId' => $entityId,
                'amount' => $amount,
                'currency' => 'EUR',
                'paymentBrand' => 'VISA',
                'card' => [
                    'number' => '4111111111111111',
                    'holder' => 'John Doe',
                    'expiryMonth' => 12,
                    'expiryYear' => 2025,
                    'cvv' => '123',
                ],
            ],
        ]);

        $data = $response->toArray();

        return new PaymentResponse(
            $data['currency'],
            $data['id'],
            '4111111111111111',
            (new \DateTime())->format('Y-m-d H:i:s'),
            2025,
            12,
            $amount,
            '123'
        );
    }
}
