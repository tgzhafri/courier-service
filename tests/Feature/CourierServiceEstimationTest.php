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
        $this->artisan('app:courier-estimate test')
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("PKG1 0 175")
            ->expectsOutput("PKG2 0 275")
            ->expectsOutput("PKG3 35 665")
            ->expectsOutput("Courier service Challenge 1 --finished--");
    }

    public function test_courier_estimation_command_with_correct_input(): void
    {
        $this->artisan('app:courier-estimate PKG5 100 150 OFR001')
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("PKG5 185 1665")
            ->expectsOutput("Courier service Challenge 1 --finished--");
    }

    public function test_courier_estimation_command_with_wrong_input(): void
    {
        $this->artisan('app:courier-estimate PKG5 abc def xyz')
            ->assertSuccessful()
            ->expectsOutput("Courier service Challenge 1 --started--")
            ->expectsOutput("input at index 1 and 2 must be an integer")
            ->expectsOutput("Courier service Challenge 1 --finished--");
    }
}
