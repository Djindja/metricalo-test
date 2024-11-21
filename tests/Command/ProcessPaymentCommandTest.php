<?php

namespace App\Tests\Command;

use App\Command\ProcessPaymentCommand;
use App\DTO\PaymentResponse;
use App\Service\PaymentProcessor;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ProcessPaymentCommandTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testExecuteWithValidSystem()
    {
        $paymentResponse = new PaymentResponse(
            'USD',
            '12345',
            '4111111111111111',
            '2023-10-10 10:00:00',
            2025,
            12,
            100.0,
            '123'
        );

        $paymentProcessor = $this->createMock(PaymentProcessor::class);
        $paymentProcessor->method('processPayment')
            ->willReturn($paymentResponse);

        $command = new ProcessPaymentCommand($paymentProcessor);
        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('app:payment'));
        $commandTester->execute([
            'system' => 'shift4',
            'amount' => 100.0,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Payment successful:', $output);
        $this->assertStringContainsString('"currency": "USD"', $output);
    }

    /**
     * @throws Exception
     */
    public function testExecuteWithInvalidSystem()
    {
        $paymentProcessor = $this->createMock(PaymentProcessor::class);

        $command = new ProcessPaymentCommand($paymentProcessor);
        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('app:payment'));
        $commandTester->execute([
            'system' => 'invalid',
            'amount' => 100.0,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Invalid payment system', $output);
    }

    /**
     * @throws Exception
     */
    public function testExecuteThrowsException()
    {
        $paymentProcessor = $this->createMock(PaymentProcessor::class);
        $paymentProcessor->method('processPayment')
            ->willThrowException(new \Exception('Payment processing error'));

        $command = new ProcessPaymentCommand($paymentProcessor);
        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('app:payment'));
        $commandTester->execute([
            'system' => 'shift4',
            'amount' => 100.0,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Failed to process payment', $output);
        $this->assertStringContainsString('Payment processing error', $output);
    }
}