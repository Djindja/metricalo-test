<?php

namespace App\Command;

use App\Service\PaymentProcessor;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessPaymentCommand extends Command
{
    private PaymentProcessor $paymentProcessor;

    public function __construct(PaymentProcessor $paymentProcessor)
    {
        parent::__construct();
        $this->paymentProcessor = $paymentProcessor;
    }

    protected static $defaultName = 'app:example';

    protected function configure(): void
    {
        $this
            ->setDescription('Process a payment via Shift4 or ACI.')
            ->addArgument('system', InputArgument::REQUIRED, 'The payment system (aci|shift4)')
            ->addArgument('amount', InputArgument::REQUIRED, 'The payment amount');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $system = $input->getArgument('system');
        $amount = (float) $input->getArgument('amount');

        if (!in_array($system, ['aci', 'shift4'])) {
            $output->writeln('<error>Invalid payment system</error>');
            return Command::FAILURE;
        }

        try {
            $paymentResponse = $this->paymentProcessor->processPayment($system, $amount);
            $output->writeln('Payment successful:');
            $output->writeln(json_encode($paymentResponse, JSON_PRETTY_PRINT));
            return Command::SUCCESS;
        } catch (Exception $e) {
            $output->writeln('<error>Failed to process payment</error>');
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }
}
