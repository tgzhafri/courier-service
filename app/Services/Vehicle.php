<?php

namespace App\Services;

class Vehicle
{
    public function list($numOfVehicle): array
    {
        // Create vehicles list
        $vehicles = array();
        for ($i = 1; $i <= $numOfVehicle; $i++) {
            $vehicles[] = ['id' => $i, 'return_time' => 0];
        }
        return $vehicles;
    }
}
