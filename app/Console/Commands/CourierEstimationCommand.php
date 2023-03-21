<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CourierEstimationCommand extends Command
{
    const BASE_DELIVERY_COST = 100;
    const COST_PER_WEIGHT_KG = 10;
    const COST_PER_DISTANCE_KM = 5;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:courier-estimate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Coding challenge for courier service to get delivery cost estimation with offer and delivery time estimation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $offerJson = File::get("database/data/offer.json");
        $offerData = collect(json_decode($offerJson, true));

        $inputJson = File::get("database/data/input.json");
        $inputData = collect(json_decode($inputJson, true));

        $data = $inputData->map(function ($item) use ($offerData) {
            $cost = $this->calculateDeliveryCost($offerData, $item);

            $discountPercentage = $this->checkApplicableDiscount($offerData, $item);

            if ($discountPercentage) {
                $total = $this->calculateTotalAfterDiscount($cost, $discountPercentage);
                $discount = $cost - $total;
            } else {
                $total = $cost;
                $discount = 0;
            }

            $this->info($item['name'] . " $discount $total");
        });

        $this->info('Courier service Challenge 1');
    }

    /**
     * Delivery cost calculation = Base Delivery Cost + (Package Total Weight * 10) + (Distance to Destination * 5)
     *
     * @return int
     */
    public function calculateDeliveryCost($offerData, $item): int
    {
        return self::BASE_DELIVERY_COST + $item['weight'] * self::COST_PER_WEIGHT_KG + $item['distance'] * self::COST_PER_DISTANCE_KM;
    }

    /**
     *  check if discount applicable based on distance and weight criteria
     *
     * @return int
     */
    public function checkApplicableDiscount($offerData, $item): int
    {
        $offerCriteria = $offerData->firstWhere('code', $item['offer_code']);

        if (
            $offerCriteria['max_distance'] >= $item['distance']
            && $offerCriteria['min_distance'] <= $item['distance']
            && $offerCriteria['max_weight'] >= $item['weight']
            && $offerCriteria['min_weight'] <= $item['weight']
        ) {
            return $offerCriteria['discount'];
        }
        return 0;
    }

    public function calculateTotalAfterDiscount($total, $discount): int
    {
        return $total * (100 - $discount) / 100;
    }
}
