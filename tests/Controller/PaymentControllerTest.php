<?php

namespace App\Tests\Controller;

use App\Controller\PaymentController;
use App\DTO\PaymentResponse;
use App\Service\PaymentProcessor;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class PaymentControllerTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testProcessPaymentWithValidSystem()
    {
        $paymentResponse = new PaymentResponse(
            'USD',
            '12345',
            '4242000000000083',
            '2023-10-10 10:00:00',
            2025,
            12,
            100.0,
            '123'
        );

        $paymentProcessor = $this->createMock(PaymentProcessor::class);
        $paymentProcessor->method('processPayment')
            ->willReturn($paymentResponse);


        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')
            ->willReturn(true);
        $container->method('get')
            ->willReturn($paymentProcessor);

        $controller = new PaymentController($paymentProcessor);
        $controller->setContainer($container);

        $controller->setContainer($this->createMock(ContainerInterface::class));

        $response = $controller->processPayment('shift4', 100.0);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'currency' => 'USD',
                'cardNumber' => '4242000000000083',
                'transactionId' => '12345',
                'creationDate' => '2023-10-10 10:00:00',
                'cardExpYear' => 2025,
                'cardExpMonth' => 12,
                'amount' => 100.0,
                'cardCvv' => '123'
            ]),
            $response->getContent()
        );
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws Exception
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testProcessPaymentWithInvalidSystem()
    {
        $paymentProcessor = $this->createMock(PaymentProcessor::class);
        $controller = new PaymentController($paymentProcessor);
        $response = $controller->processPayment('invalid', 100.0);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'Invalid payment system']),
            $response->getContent()
        );
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws Exception
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testProcessPaymentThrowsException()
    {
        $paymentProcessor = $this->createMock(PaymentProcessor::class);
        $paymentProcessor->method('processPayment')
            ->willThrowException(new \Exception('Payment processing error'));

        $controller = new PaymentController($paymentProcessor);
        $response = $controller->processPayment('shift4', 100.0);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'Failed to process payment', 'details' => 'Payment processing error']),
            $response->getContent()
        );
    }
}