<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TimeEstimationCommand extends Command
{
    const BASE_DELIVERY_COST = 100;
    const COST_PER_WEIGHT_KG = 10;
    const COST_PER_DISTANCE_KM = 5;

    const NO_OF_VEHICLES = 2;
    const MAX_SPEED = 70;
    const MAX_WEIGHT = 200;

    protected $offerFilePath = "database/data/offer.json";
    protected $inputFilePath = "database/data/input_C2.json";
    protected $testMissing = "database/data/inputMissingData.json";
    protected $testMultiple = "database/data/inputMultipleCombinePackages.json";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courier:delivery-estimate {input?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Coding challenge 2 for courier service to get delivery cost estimation with offer and delivery time estimation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->comment('Courier service Challenge 2 --started--');

        $data = $this->getData();

        $result = collect();
        $vehicles = array();

        $this->vehicleList($vehicles);

        $this->calculateDeliveryTime($data, $result, $vehicles);

        $this->table(
            ['name', 'weight', 'distance', 'offer_code', 'cost', 'time'],
            $result->sortBy('name')->toArray()
        );
        $this->comment('Courier service Challenge 2 --finished--');
    }

    public function getData()
    {
        switch ($this->argument('input')) {
            case null:
                $data = $this->parseJsonData($this->inputFilePath);
                break;
            case 'test-missing':
                $data = $this->parseJsonData($this->testMissing);
                break;
            case 'test-multiple':
                $data = $this->parseJsonData($this->testMultiple);
                break;
            default:
                $data = $this->parseJsonData($this->inputFilePath);
                break;
        }

        return collect($data)->filter(function ($item, $index) {
            $this->validateInput($item, $index);
            if (
                is_string($item['name'])
                && $item['name'] != ''
                && is_numeric($item['weight'])
                && is_numeric($item['weight'])
                && is_string($item['offer_code'])
                && $item['offer_code'] != ''
            ) {
                return $item;
            }
        })->values()->toArray();
    }

    public function parseJsonData($filePath)
    {
        $jsonData = File::get($filePath);
        return json_decode($jsonData, true);
    }

    /**
     * Delivery cost calculation = Base Delivery Cost + (Package Total Weight * 10) + (Distance to Destination * 5)
     *
     * @return int
     */
    public function calculateDeliveryCost($item): int
    {
        return self::BASE_DELIVERY_COST + $item['weight'] * self::COST_PER_WEIGHT_KG + $item['distance'] * self::COST_PER_DISTANCE_KM;
    }

    /**
     *  check if discount applicable based on distance and weight criteria
     *
     * @return int
     */
    public function getApplicableDiscount($item, $cost): int
    {
        $offer = collect($this->parseJsonData($this->offerFilePath));
        $offerCriteria = $offer->firstWhere('code', $item['offer_code']);
        $discountPercentage = 0;

        if (
            $offerCriteria
            && $offerCriteria['max_distance'] >= $item['distance']
            && $offerCriteria['min_distance'] <= $item['distance']
            && $offerCriteria['max_weight'] >= $item['weight']
            && $offerCriteria['min_weight'] <= $item['weight']
        ) {
            $discountPercentage = $offerCriteria['discount'];
        }
        return $this->calculateDiscount($cost, $discountPercentage);
    }

    public function calculateDiscount($cost, $discount): int
    {
        return $cost *  $discount / 100;
    }

    public function getDeliveryTime($item): float
    {
        $time = $item['distance'] / self::MAX_SPEED;

        // to round down answer to 2 decimal places
        return floor($time * 100) / 100;
    }

    public function vehicleList(&$vehicles)
    {
        // Create vehicles
        for ($i = 1; $i <= self::NO_OF_VEHICLES; $i++) {
            array_push($vehicles, ['id' => $i, 'return_time' => 0]);
        }
    }

    public function validateInput(array $item, int $index): void
    {
        if (
            !is_string($item['name'])
            || $item['name'] == ''
            || !$item['name']
        ) {
            $this->error("Index $index, name is missing and must be a string");
        }
        if (
            !is_string($item['offer_code'])
            || !$item['offer_code']
            || $item['offer_code'] == ''
        ) {
            $this->error("Index $index, offer_code is missing and must be a string");
        }
        if (
            !is_numeric($item['weight'])
            || !$item['weight']
        ) {
            $this->error("Index $index, weight is missing and must be a numeric");
        }
        if (
            !is_numeric($item['distance'])
            || !$item['distance']
        ) {
            $this->error("Index $index, distance is missing and must be a numeric");
        }
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

    public function calculateDeliveryForCombinedPackages($combinedPackages, $data, $result, &$vehicles): array
    {
        $combinedPackages = collect($data)->whereIn('name', $combinedPackages)->sortByDesc('distance')->values();

        // Process combined packages
        foreach ($combinedPackages as $index => $package) {
            $time = $this->getDeliveryTime($package);
            $cost = $this->calculateDeliveryCost($package);
            $discount = $this->getApplicableDiscount($package, $cost);
            $total = $cost - $discount;
            $returnTime = $time * 2;

            // Assign the package to a vehicle with the earliest return time
            foreach ($vehicles as &$vehicle) {
                if ($vehicle['return_time'] == 0 && $index == 0) {
                    $vehicle['return_time'] = $returnTime;
                    break;
                }
            }

            $merged = array_merge($package, ['cost' => $total, 'time' => $time]);
            $result->push($merged);
            $this->info($package['name'] . " $discount $total $time");
        }
        return $combinedPackages->toArray();
    }

    public function calculateDeliveryForSinglePackage($remainingPackages, $result, &$vehicles): void
    {
        foreach ($remainingPackages as $index => $package) {
            $time = $this->getDeliveryTime($package);
            $cost = $this->calculateDeliveryCost($package);
            $discount = $this->getApplicableDiscount($package, $cost);
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

            $merged = array_merge($package, ['cost' => $total, 'time' => $time]);
            $result->push($merged);
            $this->info($package['name'] . " $discount $total $time");
        }
    }

    public function calculateDeliveryTime($data, $result, $vehicles)
    {
        // Get the combined packages with maximum weight
        $combinedPackages = $this->maxLoadCombination($data, self::MAX_WEIGHT);

        // Calculate combined packages
        $combinedPackages = $this->calculateDeliveryForCombinedPackages($combinedPackages, $data, $result, $vehicles);

        // Sort packages by weight in descending order
        $remainingPackages = collect($data)->whereNotIn('name', collect($combinedPackages)->pluck('name')->toArray())->sortByDesc('weight')->values();

        // If there is no more combined packages, then calculate each package individually
        // else recursively calculate for combined packages
        if ($combinedPackages == []) {
            // Calculate remaining packages delivered individually
            $this->calculateDeliveryForSinglePackage($remainingPackages, $result, $vehicles);
        } else {
            // Calculate remaining combined packages delivery
            $this->calculateDeliveryTime($remainingPackages->toArray(), $result, $vehicles);
        }
    }
}
