<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CourierEstimationCommand extends Command
{
    const BASE_DELIVERY_COST = 100;
    const COST_PER_WEIGHT_KG = 10;
    const COST_PER_DISTANCE_KM = 5;

    protected $offer, $data;

    public function __construct()
    {
        parent::__construct();
        $offerJson = File::get("database/data/offer.json");
        $this->offer = collect(json_decode($offerJson, true));

        $inputJson = File::get("database/data/input.json");
        $this->data = collect(json_decode($inputJson, true));
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:courier-estimate {input*}';

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
        $this->info('Courier service Challenge 1 --started--');

        $input = $this->argument('input');

        $data = $this->getData($input);

        $data->map(function ($item, $key) {
            $cost = $this->calculateDeliveryCost($item);

            $discount = $this->getApplicableDiscount($item, $cost);

            $total = $cost - $discount;

            $this->info($item['name'] . " $discount $total");
        });

        $this->info('Courier service Challenge 1 --finished--');
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
            $offerCriteria['max_distance'] >= $item['distance']
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
            $keys = ['name', 'weight', 'distance', 'offer_code'];
            $data = collect([array_combine($keys, $input)]);
        } else {
            $data = $this->data;
        }
        return $data;
    }
}
