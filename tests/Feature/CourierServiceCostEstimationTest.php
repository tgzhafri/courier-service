<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CourierServiceCostEstimationTest extends TestCase
{
    public function test_delivery_cost_estimation_without_input_return_error(): void
    {
        $this->artisan('courier:cost-estimate')
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("Invalid input");
    }

    public function test_delivery_cost_estimation_with_correct_multiple_package_input(): void
    {
        $this->artisan("courier:cost-estimate '100 3
            PKG1 5 5 OFR001
            PKG2 15 5 OFR002
            PKG3 10 100 OFR003'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("PKG1 0 175")
            ->expectsOutput("PKG2 0 275")
            ->expectsOutput("PKG3 35 665")
            ->expectsOutput("Courier service Challenge 1 --finished--");
    }

    public function test_delivery_cost_estimation_with_correct_single_input(): void
    {
        $this->artisan("courier:cost-estimate '100 3
            PKG3 10 100 OFR003'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("PKG3 35 665")
            ->expectsOutput("Courier service Challenge 1 --finished--");
    }

    public function test_delivery_cost_estimation_with_wrong_input(): void
    {
        $this->artisan('courier:cost-estimate PKG5 abc def xyz')
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("Invalid input");

        $this->artisan("courier:cost-estimate '100 3 234 2
            PKG1 5 5 OFR001
            PKG2 15 5 OFR002
            PKG3 10 100 OFR003'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("Invalid input");
    }

    public function test_delivery_cost_estimation_with_incomplete_input(): void
    {
        $this->artisan("courier:cost-estimate '100 3
            null 5 OFR001
            PKG2 15 null OFR002
            PKG3 10 100 OFR003'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("Invalid input");
    }

    public function test_delivery_cost_estimation_with_missing_base_input(): void
    {
        $this->artisan("courier:cost-estimate '
            null 5 OFR001
            PKG2 15 null OFR002
            PKG3 10 100 OFR003'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("Invalid input");

        $this->artisan("courier:cost-estimate '
            PKG1 5 5 OFR001
            PKG2 15 5 OFR002
            PKG3 10 100 OFR003'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("Invalid input");
    }

    public function test_delivery_cost_estimation_with_missing_package_input(): void
    {
        $this->artisan("courier:cost-estimate '100 3'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("Invalid input");
    }

    public function test_delivery_cost_estimation_with_negative_input(): void
    {
        $this->artisan("courier:cost-estimate ' -100 3
            PKG1 -5 5 OFR001
            PKG2 -15 5 OFR002
            PKG3 10 -100 OFR003'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("Invalid input");
    }

    public function test_delivery_cost_estimation_with_very_large_input(): void
    {
        $this->artisan("courier:cost-estimate '100 3
            PKG1 5123 5 OFR001
            PKG2 15 53452345 OFR002
            PKG3 10214234 100 OFR003'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("PKG1 0 175")
            ->expectsOutput("PKG2 0 275")
            ->expectsOutput("PKG3 35 665")
            ->expectsOutput("Courier service Challenge 1 --finished--");
    }

    public function test_delivery_cost_estimation_with_spacing_in_input(): void
    {
        $this->artisan("courier:cost-estimate '100 3

            PKG1 5 5 OFR001

            PKG2 15 5 OFR002



            PKG3 10 100 OFR003'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("PKG1 0 175")
            ->expectsOutput("PKG2 0 275")
            ->expectsOutput("PKG3 35 665")
            ->expectsOutput("Courier service Challenge 1 --finished--");
    }

    public function test_delivery_cost_estimation_without_spacing_in_input(): void
    {
        $this->artisan("courier:cost-estimate '100 3

            PKG1 5 5 OFR001

            PKG2 15 5 OFR002   PKG3 10 100 OFR003'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("PKG1 0 175")
            ->expectsOutput("PKG2 0 275")
            ->expectsOutput("PKG3 35 665")
            ->expectsOutput("Courier service Challenge 1 --finished--");
    }
}
