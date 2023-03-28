<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CourierServiceTimeEstimationTest extends TestCase
{
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
            ->expectsOutput("PKG1 0 750 3.98")
            ->expectsOutput("PKG2 0 1475 1.78")
            ->expectsOutput("PKG3 0 2350 1.42")
            ->expectsOutput("PKG4 105 1395 0.85")
            ->expectsOutput("PKG5 0 2125 4.19")
            ->expectsOutput("Courier service Challenge 2 --finished--");
    }

    public function test_delivery_time_estimation_with_correct_input_with_spacing(): void
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
            ->expectsOutput("PKG1 0 750 3.98")
            ->expectsOutput("PKG2 0 1475 1.78")
            ->expectsOutput("PKG3 0 2350 1.42")
            ->expectsOutput("PKG4 105 1395 0.85")
            ->expectsOutput("PKG5 0 2125 4.19")
            ->expectsOutput("Courier service Challenge 2 --finished--");
    }

    public function test_delivery_time_estimation_with_correct_input_without_spacing(): void
    {
        $this->artisan("courier:delivery-estimate '100 5
            PKG1 50 30 OFR001
            PKG2 75 125 OFR008     PKG3 175 100 OFR003
            PKG4 110 60 OFR002 PKG5 155 95 NA 2 70 200'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 2 --started--")
            ->expectsOutput("Invalid input");
    }

    public function test_delivery_time_estimation_with_negative_input(): void
    {
        $this->artisan("courier:delivery-estimate ' -100 5
            PKG1 -50 30 OFR001
            PKG2 75 -125 OFR008
            PKG3 -175 100 OFR003
            PKG4 -110 60 OFR002
            PKG5 155 95 NA
            2 70 -200'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 2 --started--")
            ->expectsOutput("Invalid input");

        $this->artisan("courier:delivery-estimate '100 5
            PKG1 -50 30 OFR001
            PKG2 75 -125 OFR008
            PKG3 -175 100 OFR003
            PKG4 -110 60 OFR002
            PKG5 155 95 NA
            2 70 -200'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 2 --started--")
            ->expectsOutput("Invalid input");
    }

    public function test_delivery_time_estimation_with_very_large_input(): void
    {
        $this->artisan("courier:delivery-estimate '100 5
            PKG1 5000 30 OFR001
            PKG2 75 125000 OFR008
            PKG3 175000 100 OFR003
            PKG4 110 6000 OFR002
            PKG5 155 95 NA
            123456 70 20000'")
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 2 --started--")
            ->expectsOutput("Invalid input");
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
