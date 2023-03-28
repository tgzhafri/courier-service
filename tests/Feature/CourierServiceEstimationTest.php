<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CourierServiceEstimationTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_cost_estimation_without_input_return_default_data(): void
    {
        $this->artisan('courier:cost-estimate')
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("PKG1 0 175")
            ->expectsOutput("PKG2 0 275")
            ->expectsOutput("PKG3 35 665")
            ->expectsOutput("Courier service Challenge 1 --finished--");
    }

    public function test_cost_estimation_with_correct_multiple_package_input(): void
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

    public function test_cost_estimation_with_correct_multiple_single_input(): void
    {
        $this->artisan("courier:cost-estimate '100 3
            PKG3 10 100 OFR003'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("PKG3 35 665")
            ->expectsOutput("Courier service Challenge 1 --finished--");
    }

    public function test_cost_estimation_with_wrong_input(): void
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

    public function test_cost_estimation_with_incomplete_input(): void
    {
        $this->artisan("courier:cost-estimate '100 3
            null 5 OFR001
            PKG2 15 null OFR002
            PKG3 10 100 OFR003'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("Invalid input");
    }

    public function test_cost_estimation_with_missing_base_input(): void
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

    public function test_cost_estimation_with_missing_package_input(): void
    {
        $this->artisan("courier:cost-estimate '100 3'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("Invalid input");
    }

    public function test_courier_delivery_estimation_without_input(): void
    {
        $this->artisan('courier:delivery-estimate')
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 2 --started--")
            ->expectsOutput("PKG2 0 1475 1.78")
            ->expectsOutput("PKG4 105 1395 0.85")
            ->expectsOutput("PKG3 0 2350 1.42")
            ->expectsOutput("PKG5 0 2125 4.19")
            ->expectsOutput("PKG1 0 750 3.98")
            ->expectsOutput("Courier service Challenge 2 --finished--");
    }

    public function test_courier_delivery_estimation_with_test_input(): void
    {
        $this->artisan('courier:delivery-estimate test')
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 2 --started--")
            ->expectsOutput("PKG2 0 1475 1.78")
            ->expectsOutput("PKG4 105 1395 0.85")
            ->expectsOutput("PKG3 0 2350 1.42")
            ->expectsOutput("PKG5 0 2125 4.19")
            ->expectsOutput("PKG1 0 750 3.98")
            ->expectsOutput("Courier service Challenge 2 --finished--");
    }

    public function test_courier_delivery_estimation_with_multiple_combined_packages(): void
    {
        $this->artisan('courier:delivery-estimate test-multiple')
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 2 --started--")
            ->expectsOutput("PKG2 0 1475 1.78")
            ->expectsOutput("PKG10 0 1925 7.61")
            ->expectsOutput("Courier service Challenge 2 --finished--");
    }

    public function test_courier_delivery_estimation_with_missing_data(): void
    {
        $this->artisan('courier:delivery-estimate test-missing')
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 2 --started--")
            ->expectsOutput("Index 0, name is missing and must be a string")
            ->expectsOutput("Index 1, distance is missing and must be a numeric")
            ->expectsOutput("Index 4, weight is missing and must be a numeric")
            ->expectsOutput("Index 8, name is missing and must be a string")
            ->expectsOutput("Index 9, offer_code is missing and must be a string")
            ->expectsOutput("PKG8 62 1188 1.42")
            ->expectsOutput("PKG6 0 750 0.42")
            ->expectsOutput("PKG2 0 850 0")
            ->expectsOutput("PKG7 0 1725 1.78")
            ->expectsOutput("PKG4 98 1302 0.85")
            ->expectsOutput("PKG3 0 2350 4.26")
            ->expectsOutput("Courier service Challenge 2 --finished--");
    }
}
