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
    public function test_courier_estimation_command_without_correct_input(): void
    {
        $this->artisan('courier:cost-estimate test')
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("PKG1 0 175")
            ->expectsOutput("PKG2 0 275")
            ->expectsOutput("PKG3 35 665")
            ->expectsOutput("Courier service Challenge 1 --finished--");

        $this->artisan('courier:cost-estimate test 123 1231 123 123123 123')
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("PKG1 0 175")
            ->expectsOutput("PKG2 0 275")
            ->expectsOutput("PKG3 35 665")
            ->expectsOutput("Courier service Challenge 1 --finished--");
    }

    public function test_courier_estimation_command_with_correct_input(): void
    {
        $this->artisan('courier:cost-estimate PKG5 100 150 OFR001')
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("PKG5 185 1665")
            ->expectsOutput("Courier service Challenge 1 --finished--");
    }

    public function test_courier_estimation_command_with_wrong_input(): void
    {
        $this->artisan('courier:cost-estimate PKG5 abc def xyz')
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("input at index 1 and 2 must be an integer")
            ->expectsOutput("Courier service Challenge 1 --finished--");
    }

    public function test_courier_delivery_estimation_without_test_input(): void
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
            ->expectsOutput("PKG6 0 1515 1.78")
            ->expectsOutput("PKG7 80 1520 1.42")
            ->expectsOutput("PKG4 98 1302 0.85")
            ->expectsOutput("PKG3 80 1520 1.42")
            ->expectsOutput("PKG9 257 2318 3.05")
            ->expectsOutput("PKG5 0 2325 5.75")
            ->expectsOutput("PKG10 0 2325 4.91")
            ->expectsOutput("PKG2 0 1415 8.04")
            ->expectsOutput("PKG8 0 890 7.95")
            ->expectsOutput("PKG1 0 350 9.22")
            ->expectsOutput("Courier service Challenge 2 --finished--");
    }
}
