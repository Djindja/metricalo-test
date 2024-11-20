<?php

namespace App\Tests\Service;

use App\DTO\PaymentResponse;
use App\Service\PaymentProcessor;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class PaymentProcessorTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testProcessShift4Payment()
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $response->method('toArray')->willReturn([
            'currency' => 'USD',
            'id' => 'txn_12345',
            'source' => ['number' => '4111111111111111']
        ]);

        $httpClient->method('request')->willReturn($response);

        $paymentProcessor = new PaymentProcessor($httpClient);
        $paymentResponse = $paymentProcessor->processPayment('shift4', 100.0);

        $this->assertInstanceOf(PaymentResponse::class, $paymentResponse);
        $this->assertEquals('USD', $paymentResponse->currency);
        $this->assertEquals('txn_12345', $paymentResponse->transactionId);
        $this->assertEquals('4111111111111111', $paymentResponse->cardNumber);
        $this->assertEquals(2025, $paymentResponse->cardExpYear);
        $this->assertEquals(12, $paymentResponse->cardExpMonth);
        $this->assertEquals(100.0, $paymentResponse->amount);
        $this->assertEquals('123', $paymentResponse->cardCvv);
    }

    /**
     * @throws Exception
     */
    public function testProcessAciPayment()
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $response->method('toArray')->willReturn([
            'currency' => 'EUR',
            'id' => 'txn_67890'
        ]);

        $httpClient->method('request')->willReturn($response);

        $paymentProcessor = new PaymentProcessor($httpClient);
        $paymentResponse = $paymentProcessor->processPayment('aci', 100.0);

        $this->assertInstanceOf(PaymentResponse::class, $paymentResponse);
        $this->assertEquals('EUR', $paymentResponse->currency);
        $this->assertEquals('txn_67890', $paymentResponse->transactionId);
        $this->assertEquals('4111111111111111', $paymentResponse->cardNumber);
        $this->assertEquals(2025, $paymentResponse->cardExpYear);
        $this->assertEquals(12, $paymentResponse->cardExpMonth);
        $this->assertEquals(100.0, $paymentResponse->amount);
        $this->assertEquals('123', $paymentResponse->cardCvv);
    }

    /**
     * @throws Exception
     */
    public function testProcessPaymentWithInvalidSystem()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported payment system: invalid');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $paymentProcessor = new PaymentProcessor($httpClient);
        $paymentProcessor->processPayment('invalid', 100.0);
    }
}