<?php

namespace App\Services;

class CostCalculator
{
    const BASE_COST_DELIVERY = 100;
    const COST_PER_WEIGHT_KG = 10;
    const COST_PER_DISTANCE_KM = 5;

    /**
     * Calculate delivery cost for a given package
     *
     * @param array $package
     * @return int
     */
    public function calculate(array $package, $base): int
    {
        $weight = $package['weight'];
        $distance = $package['distance'];

        $cost = $base['base_delivery_cost'] ?? self::BASE_COST_DELIVERY
            + ($weight * self::COST_PER_WEIGHT_KG) + ($distance * self::COST_PER_DISTANCE_KM);

        return $cost;
    }
}
