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
    public function test_delivery_cost_estimation_without_input_return_default_data(): void
    {
        $this->artisan('courier:cost-estimate')
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("PKG1 0 175")
            ->expectsOutput("PKG2 0 275")
            ->expectsOutput("PKG3 35 665")
            ->expectsOutput("Courier service Challenge 1 --finished--");
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

    public function test_delivery_cost_estimation_with_correct_multiple_single_input(): void
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

    public function test_delivery_time_estimation_without_correct_input_return_error(): void
    {
        $this->artisan('courier:delivery-estimate test')
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 2 --started--")
            ->expectsOutput("Invalid input");
    }

    public function test_delivery_time_estimation_with_correct_input(): void
    {
        $this->artisan("courier:delivery-estimate '100 5
            PKG1 50 30 OFR001
            PKG2 75 125 OFR008
            PKG3 175 100 OFR003
            PKG4 110 60 OFR002
            PKG5 155 95 NA
            2 70 200'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 2 --started--")
            ->expectsOutput("PKG2 0 1475 1.78")
            ->expectsOutput("PKG4 105 1395 0.85")
            ->expectsOutput("PKG3 0 2350 1.42")
            ->expectsOutput("PKG5 0 2125 4.19")
            ->expectsOutput("PKG1 0 750 3.98")
            ->expectsOutput("Courier service Challenge 2 --finished--");
    }

    public function test_delivery_time_estimation_with_incorrect_package_input(): void
    {
        $this->artisan("courier:delivery-estimate '100 5
            50 30 OFR001
            PKG2 null 125 OFR008
            PKG3 175  OFR003
            PKG4 110 null OFR002
            PKG5 155 95 NA
            2 70 200'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 2 --started--")
            ->expectsOutput("Invalid input");

        $this->artisan("courier:delivery-estimate '100 5
            PKG1 50 30 OFR001 12
            PKG2 75 125 OFR008 xx
            PKG3 175 100 OFR003
            PKG4 110 60 OFR002
            PKG5 155 95 NA
            2 70 200'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 2 --started--")
            ->expectsOutput("Invalid input");
    }

    public function test_delivery_time_estimation_with_incorrect_base_input(): void
    {
        $this->artisan("courier:delivery-estimate '5
            PKG1 50 30 OFR001
            PKG2 75 125 OFR008
            PKG3 175 100 OFR003
            PKG4 110 60 OFR002
            PKG5 155 95 NA
            2 70 200'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 2 --started--")
            ->expectsOutput("Invalid input");

        $this->artisan("courier:delivery-estimate '100 5 20 xyz
            PKG1 50 30 OFR001
            PKG2 75 125 OFR008
            PKG3 175 100 OFR003
            PKG4 110 60 OFR002
            PKG5 155 95 NA
            2 70 200'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 2 --started--")
            ->expectsOutput("Invalid input");
    }

    public function test_delivery_time_estimation_with_incorrect_vehicle_input(): void
    {
        $this->artisan("courier:delivery-estimate '5
            PKG1 50 30 OFR001
            PKG2 75 125 OFR008
            PKG3 175 100 OFR003
            PKG4 110 60 OFR002
            PKG5 155 95 NA
            xyz null 200'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 2 --started--")
            ->expectsOutput("Invalid input");

        $this->artisan("courier:delivery-estimate '100 5 20 xyz
            PKG1 50 30 OFR001
            PKG2 75 125 OFR008
            PKG3 175 100 OFR003
            PKG4 110 60 OFR002
            PKG5 155 95 NA
            null'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 2 --started--")
            ->expectsOutput("Invalid input");

        $this->artisan("courier:delivery-estimate '100 5 20 xyz
            PKG1 50 30 OFR001
            PKG2 75 125 OFR008
            PKG3 175 100 OFR003
            PKG4 110 60 OFR002
            PKG5 155 95 NA
            2 70 200 300 xyz'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 2 --started--")
            ->expectsOutput("Invalid input");
    }
}
