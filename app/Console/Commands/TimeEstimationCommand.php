<?php

namespace App\Console\Commands;

use App\Services\CostCalculator;
use App\Services\DataReader;
use App\Services\DiscountCalculator;
use App\Services\InputValidator;
use App\Services\Formatter;
use App\Services\Vehicle;
use Illuminate\Console\Command;

class TimeEstimationCommand extends Command
{
    protected $inputValidator;
    protected $dataReader;
    protected $costCalculator;
    protected $discountCalculator;
    protected $formatter;
    protected $vehicle;

    public function __construct(
        InputValidator $inputValidator,
        DataReader $dataReader,
        CostCalculator $costCalculator,
        DiscountCalculator $discountCalculator,
        Formatter $formatter,
        Vehicle $vehicle,
    ) {
        parent::__construct();
        $this->inputValidator = $inputValidator;
        $this->dataReader = $dataReader;
        $this->costCalculator = $costCalculator;
        $this->discountCalculator = $discountCalculator;
        $this->formatter = $formatter;
        $this->vehicle = $vehicle;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courier:delivery-estimate {input*} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Coding challenge 2 for courier service to get delivery time estimation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->comment('Courier service Challenge 2 --started--');

        $input = $this->argument('input');
        $formatted = array();
        $result = collect();

        if (!empty($input)) {
            $formatted = $this->formatter->timeInputToArray($input[0]);
            if (!$this->inputValidator->validate($formatted)) {
                return $this->error('Invalid input');
            }
        }

        $data = $this->dataReader->readData($formatted);

        if (empty($data)) {
            return $this->error('Data is empty');
        }

        if (!isset($data['vehicle'])) {
            return $this->error('Vehicle base data is required');
        }
        $baseVehicle = $data['vehicle'];
        $vehicles = $this->vehicle->list($baseVehicle['no_of_vehicles']);

        $this->calculateDeliveryTime($data['packages'] ?? $data, $result, $vehicles, $baseVehicle);

        $this->table(
            ['name', 'weight', 'distance', 'offer_code', 'cost', 'time'],
            $result->sortBy('name')->toArray()
        );

        $this->comment('Courier service Challenge 2 --finished--');
    }

    public function calculateDeliveryTime($data, $result, $vehicles, $base)
    {
        // Get the combined packages with maximum weight
        $combinedPackages = $this->maxLoadCombination($data, $base['max_weight']);

        // Calculate combined packages
        $combinedPackages = $this->calculateDeliveryForCombinedPackages($combinedPackages, $data, $result, $vehicles, $base);

        // Sort packages by weight in descending order
        $remainingPackages = collect($data)->whereNotIn('name', collect($combinedPackages)->pluck('name')->toArray())->sortByDesc('weight')->values();

        // If there is no more combined packages, then calculate each package individually
        // else recursively calculate for combined packages
        if ($combinedPackages == []) {
            // Calculate remaining packages delivered individually
            $this->calculateDeliveryForSinglePackage($remainingPackages, $result, $vehicles, $base);
        } else {
            // Calculate remaining combined packages delivery
            $this->calculateDeliveryTime($remainingPackages->toArray(), $result, $vehicles, $base);
        }
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

            $merged = array_merge($package, ['cost' => $total, 'time' => $time]);
            $result->push($merged);
            $this->info($package['name'] . " $discount $total $time");
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

            $merged = array_merge($package, ['cost' => $total, 'time' => $time]);
            $result->push($merged);
            $this->info($package['name'] . " $discount $total $time");
        }
    }
}
