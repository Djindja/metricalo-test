<?php

namespace App\DTO;

class PaymentResponse
{
    public string $currency;
    public string $transactionId;
    public string $cardNumber;
    public string $creationDate;
    public int $cardExpYear;
    public int $cardExpMonth;
    public float $amount;
    public string $cardCvv;

    public function __construct(string $currency, string $transactionId, string $cardNumber, string $creationDate, int $cardExpYear, int $cardExpMonth, float $amount, string $cardCvv)
    {
        $this->currency = $currency;
        $this->transactionId = $transactionId;
        $this->cardNumber = $cardNumber;
        $this->creationDate = $creationDate;
        $this->cardExpYear = $cardExpYear;
        $this->cardExpMonth = $cardExpMonth;
        $this->amount = $amount;
        $this->cardCvv = $cardCvv;
    }
}