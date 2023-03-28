<?php

namespace App\Services;

class DeliveryTimeCalculator
{
    protected $costCalculator;
    protected $discountCalculator;

    public function __construct(
        CostCalculator $costCalculator,
        DiscountCalculator $discountCalculator,
    ) {
        $this->costCalculator = $costCalculator;
        $this->discountCalculator = $discountCalculator;
    }

    public function getDeliveryTime($item, $base): float
    {
        $time = $item['distance'] / $base['max_speed'];

        // to round down answer to 2 decimal places
        return floor($time * 100) / 100;
    }

    /**
     * Get the combination of items with maximum weight that can be carried
     * by a vehicle.
     * @param array $items The items to select from, with their weight and name.
     * @param int $capacity The maximum weight that can be carried by a vehicle.
     * @return array The names of the items that were selected in the optimal combination.
     */
    public function maxLoadCombination(array $items, int $capacity): array
    {
        /* This is a dynamic programming approach to solve the knapsack problem. */
        // Initialize an array with the possible maximum weight that can be carried
        $n = count($items);
        $dp = array_fill(0, $capacity + 1, 0);
        // Iterate over the items and their weights, and update the array of possible maximum weights
        for ($i = 0; $i < $n; $i++) {
            for ($j = $capacity; $j >= $items[$i]['weight']; $j--) {
                $dp[$j] = max($dp[$j], $dp[$j - $items[$i]['weight']] + 1);
            }
        }

        $result = [];
        if ($dp[$capacity] == 1) {
            // No possible combination, return the empty array
            return [];
        } else {
            // Retrieve the items that were selected in the optimal combination
            $j = $capacity;
            for ($i = $n - 1; $i >= 0; $i--) {
                if ($j >= $items[$i]['weight'] && $dp[$j] == $dp[$j - $items[$i]['weight']] + 1) {
                    $j -= $items[$i]['weight'];
                    $result[] = $items[$i]['name'];
                }
            }
        }

        // Return the names of the items that were selected in the optimal combination
        return $result;
    }

    public function calculateDeliveryForCombinedPackages($combinedPackages, $data, $result, &$vehicles, $base): array
    {
        $packages = collect($data)->whereIn('name', $combinedPackages)->sortByDesc('distance')->values();

        // Process combined packages
        foreach ($packages as $index => $package) {
            $time = $this->getDeliveryTime($package, $base);
            $cost = $this->costCalculator->calculate($package, $data['base'] ?? null);
            $discount = $this->discountCalculator->calculate($package, $cost);
            $total = $cost - $discount;
            $returnTime = $time * 2;

            // Assign the package to a vehicle with the earliest return time
            foreach ($vehicles as &$vehicle) {
                if ($vehicle['return_time'] == 0 && $index == 0) {
                    $vehicle['return_time'] = $returnTime;
                    break;
                }
            }

            $merged = array_merge($package, ['cost' => $total, 'time' => $time, 'discount' => $discount]);
            $result->push($merged);
        }
        return $packages->toArray();
    }

    public function calculateDeliveryForSinglePackage($remainingPackages, $result, &$vehicles, $base): void
    {
        foreach ($remainingPackages as $index => $package) {
            $time = $this->getDeliveryTime($package, $base);
            $cost = $this->costCalculator->calculate($package, $data['base'] ?? null);
            $discount = $this->discountCalculator->calculate($package, $cost);
            $total = $cost - $discount;
            $returnTime = $time * 2;

            // Assign the package to a vehicle with the earliest return time
            usort($vehicles, function ($a, $b) {
                return $a["return_time"] - $b["return_time"];
            });
            foreach ($vehicles as $key => &$vehicle) {
                if ($index + 1 == count($remainingPackages)) {
                    $time = $vehicle['return_time'] + $time;
                    $vehicle['return_time'] = $time;
                    break;
                }
                if ($key == 0 && $vehicle['return_time'] != 0) {
                    $time = $vehicle['return_time'] + $time;
                    $vehicle['return_time'] += $returnTime;
                    break;
                }
                if ($vehicle['return_time'] == 0) {
                    $vehicle['return_time'] = $returnTime;
                    break;
                }
            }

            $merged = array_merge($package, ['cost' => $total, 'time' => $time, 'discount' => $discount]);
            $result->push($merged);
        }
    }
}
