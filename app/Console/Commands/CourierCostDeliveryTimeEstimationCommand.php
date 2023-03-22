<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CourierCostDeliveryTimeEstimationCommand extends Command
{
    const BASE_DELIVERY_COST = 100;
    const COST_PER_WEIGHT_KG = 10;
    const COST_PER_DISTANCE_KM = 5;

    const NO_OF_VEHICLES = 2;
    const MAX_SPEED = 70;
    const MAX_WEIGHT = 200;

    protected $offer, $data;

    public function __construct()
    {
        parent::__construct();
        $offerJson = File::get("database/data/offer.json");
        $this->offer = collect(json_decode($offerJson, true));

        $inputJson = File::get("database/data/input_c2.json");
        $this->data = collect(json_decode($inputJson, true));
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courier:delivery-estimate {input*}';

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

        $input = $this->argument('input');

        $data = $this->getData($input);

        if ($data) {
            $result = collect();
            $vehicles = [];
            $packages = $data->toArray();

            for ($i = 1; $i <= self::NO_OF_VEHICLES; $i++) {
                $vehicles[] = [
                    'id' => $i,
                    'return_time' => 0
                ];
            }

            $combined = $this->maxLoadPackageCombination($data->toArray());

            // Exclude packages from initial array
            $combinedPackages = [];
            foreach ($combined as $name) {
                foreach ($packages as $key => $package) {
                    if ($package["name"] == $name) {
                        $combinedPackages[] = $package;
                        unset($packages[$key]);
                        break;
                    }
                }
            }
            // Sort packages by distance in descending order
            usort($combinedPackages, function ($a, $b) {
                return $b["distance"] - $a["distance"];
            });
            foreach ($combinedPackages as $key => $package) {
                $time = $this->getDeliveryTime($package);
                $cost = $this->calculateDeliveryCost($package);
                $discount = $this->getApplicableDiscount($package, $cost);
                $total = $cost - $discount;

                if ($key == 0) {
                    $returnTime = $time * 2;
                    foreach ($vehicles as &$vehicle) {
                        if ($vehicle['return_time'] == 0) {
                            $vehicle['return_time'] = $returnTime;
                            break;
                        }
                    }
                }
                $merged = array_merge(['time' => $time], $package);
                $result->push($merged);
                $this->info($package['name'] . " $discount $total $time");
            }

            // Sort packages by weight in descending order
            usort($packages, function ($a, $b) {
                return $b["weight"] - $a["weight"];
            });
            $numOfPackages = count($packages);
            foreach ($packages as $index => $package) {
                $cost = $this->calculateDeliveryCost($package);
                $discount = $this->getApplicableDiscount($package, $cost);
                $total = $cost - $discount;
                $time = $this->getDeliveryTime($package);
                $returnTime = $time * 2;

                // Sort vehicles by return time in ascending order
                usort($vehicles, function ($a, $b) {
                    return $a["return_time"] - $b["return_time"];
                });
                foreach ($vehicles as $key => &$vehicle) {
                    if ($index + 1 == $numOfPackages) {
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
                $merged = array_merge(['time' => $time], $package);
                $result->push($merged);
                $this->info($package['name'] . " $discount $total $time");
            }
        }

        $this->comment('Courier service Challenge 2 --finished--');
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
        $offerCriteria = $this->offer->firstWhere('code', $item['offer_code']);
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

    public function getData($input): mixed
    {
        if ($input && count($input) == 4) {
            if (!is_numeric($input[1]) || !is_numeric($input[2])) {
                $this->error('input at index 1 and 2 must be an integer');
                return null;
            }
            if (!is_string($input[0]) || !is_string($input[3])) {
                $this->error('input at index 0 and 3 must be a string');
                return null;
            }

            $keys = ['name', 'weight', 'distance', 'offer_code'];
            return collect([array_combine($keys, $input)]);
        }
        $this->question('This is default test data');
        return $this->data;
    }

    public function getDeliveryTime($item)
    {
        $time = $item['distance'] / self::MAX_SPEED;

        // to round down answer to 2 decimal places
        return floor($time * 100) / 100;
    }

    /**
     * Get the combination of max possible load the vehicle can carry
     *
     * @param array $nums
     * @param int $target
     * @param int $k
     * @return array
     */
    public function maxLoadPackageCombination($packages)
    {
        // Sort packages by weight in ascending order
        usort($packages, function ($a, $b) {
            return $a["weight"] - $b["weight"];
        });

        // Initialize pointers
        $left = 0;
        $right = count($packages) - 1;

        // Initialize closest weight sum and combination
        $closest_sum = -1;
        $combination = [];

        // Loop through packages
        while ($left < $right) {
            $weight_sum = $packages[$left]["weight"] + $packages[$right]["weight"];
            if ($weight_sum >= 200) {
                $right--;
            } else {
                // Update closest weight sum and combination if necessary
                if ($weight_sum > $closest_sum) {
                    $closest_sum = $weight_sum;
                    $combination = [$packages[$left]['name'], $packages[$right]['name']];
                }
                $left++;
            }
        }
        return $combination;
    }
}
