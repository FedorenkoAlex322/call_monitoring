<?php

namespace Tests\Unit\Services;

use App\Models\Tariff;
use App\Services\BillingService;
use PHPUnit\Framework\TestCase;

class BillingServiceTest extends TestCase
{
    private BillingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BillingService();
    }

    private function makeTariff(float $pricePerMinute, float $connectionFee = 0, int $freeSeconds = 0): Tariff
    {
        $tariff = new Tariff();
        $tariff->price_per_minute = $pricePerMinute;
        $tariff->connection_fee = $connectionFee;
        $tariff->free_seconds = $freeSeconds;
        return $tariff;
    }

    public function test_calculate_cost_basic(): void
    {
        $tariff = $this->makeTariff(1.50, 0, 0);
        $cost = $this->service->calculateCost($tariff, 60);
        $this->assertEquals('1.50', $cost);
    }

    public function test_calculate_cost_rounds_up_to_next_minute(): void
    {
        $tariff = $this->makeTariff(1.50, 0, 0);
        $cost = $this->service->calculateCost($tariff, 61);
        $this->assertEquals('3.00', $cost); // 61s -> 2 minutes
    }

    public function test_calculate_cost_with_free_seconds(): void
    {
        $tariff = $this->makeTariff(1.50, 0, 10);
        $cost = $this->service->calculateCost($tariff, 70);
        // 70 - 10 free = 60 billable -> 1 min -> 1.50
        $this->assertEquals('1.50', $cost);
    }

    public function test_calculate_cost_duration_within_free_seconds(): void
    {
        $tariff = $this->makeTariff(1.50, 0, 30);
        $cost = $this->service->calculateCost($tariff, 20);
        // 20s < 30 free -> billable = 0 -> only connection fee = 0.00
        $this->assertEquals('0.00', $cost);
    }

    public function test_calculate_cost_with_connection_fee(): void
    {
        $tariff = $this->makeTariff(0.80, 1.00, 10);
        $cost = $this->service->calculateCost($tariff, 70);
        // 70 - 10 = 60 billable -> 1 min -> 0.80 + 1.00 = 1.80
        $this->assertEquals('1.80', $cost);
    }

    public function test_calculate_cost_connection_fee_only_when_within_free(): void
    {
        $tariff = $this->makeTariff(1.50, 2.00, 30);
        $cost = $this->service->calculateCost($tariff, 10);
        // within free seconds -> only connection fee
        $this->assertEquals('2.00', $cost);
    }

    public function test_calculate_cost_zero_duration(): void
    {
        $tariff = $this->makeTariff(1.50, 0, 0);
        $cost = $this->service->calculateCost($tariff, 0);
        $this->assertEquals('0.00', $cost);
    }

    public function test_calculate_billsec_basic(): void
    {
        $tariff = $this->makeTariff(1.50, 0, 5);
        $this->assertEquals(55, $this->service->calculateBillsec($tariff, 60));
    }

    public function test_calculate_billsec_within_free(): void
    {
        $tariff = $this->makeTariff(1.50, 0, 30);
        $this->assertEquals(0, $this->service->calculateBillsec($tariff, 20));
    }

    public function test_calculate_billsec_no_free_seconds(): void
    {
        $tariff = $this->makeTariff(1.50, 0, 0);
        $this->assertEquals(60, $this->service->calculateBillsec($tariff, 60));
    }
}
