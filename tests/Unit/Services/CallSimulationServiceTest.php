<?php

namespace Tests\Unit\Services;

use App\Services\CallSimulationService;
use App\Services\BillingService;
use PHPUnit\Framework\TestCase;

class CallSimulationServiceTest extends TestCase
{
    private CallSimulationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CallSimulationService(new BillingService());
    }

    public function test_generate_destination_format(): void
    {
        $dst = $this->service->generateDestination();
        $this->assertMatchesRegularExpression('/^79\d{9}$/', $dst);
        $this->assertEquals(11, strlen($dst));
    }

    public function test_generate_destination_is_random(): void
    {
        $destinations = array_map(fn() => $this->service->generateDestination(), range(1, 10));
        $unique = array_unique($destinations);
        $this->assertGreaterThan(1, count($unique));
    }

    public function test_should_end_call_when_max_duration_reached(): void
    {
        $this->assertTrue($this->service->shouldEndCall(10, 10));
        $this->assertTrue($this->service->shouldEndCall(15, 10));
    }

    public function test_should_end_call_not_always_before_max(): void
    {
        // With 20% random chance, over 100 runs some should return false
        $results = array_map(fn() => $this->service->shouldEndCall(1, 100), range(1, 100));
        $this->assertContains(false, $results);
    }
}
