<?php

namespace App\Console\Commands;

use App\Services\CostCalculator;
use App\Services\DataReader;
use App\Services\DeliveryTimeCalculator;
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
    protected $deliveryTimeCalculator;

    public function __construct(
        InputValidator $inputValidator,
        DataReader $dataReader,
        CostCalculator $costCalculator,
        DiscountCalculator $discountCalculator,
        Formatter $formatter,
        Vehicle $vehicle,
        DeliveryTimeCalculator $deliveryTimeCalculator,
    ) {
        parent::__construct();
        $this->inputValidator = $inputValidator;
        $this->dataReader = $dataReader;
        $this->costCalculator = $costCalculator;
        $this->discountCalculator = $discountCalculator;
        $this->formatter = $formatter;
        $this->vehicle = $vehicle;
        $this->deliveryTimeCalculator = $deliveryTimeCalculator;
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

        if (empty($input)) {
            return $this->error('Input array is required');
        }

        $formatted = $this->formatter->timeInputToArray($input[0]);

        if (!$this->inputValidator->validate($formatted)) {
            return $this->error('Invalid input');
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

        $this->calculateDeliveryTime($data['packages'], $result, $vehicles, $baseVehicle);

        // Use the map() method to convert each object into a string to display on console
        collect($result)->sortBy('name')->each(function ($obj) {
            $this->info($obj['name'] . ' ' . ($obj['discount']) . ' ' . $obj['cost'] . ' ' . $obj['time']);
        });

        $this->table(
            ['name', 'weight', 'distance', 'offer_code', 'cost', 'time'],
            $result->sortBy('name')->toArray()
        );

        $this->comment('Courier service Challenge 2 --finished--');
    }

    public function calculateDeliveryTime($data, $result, $vehicles, $base): void
    {
        // Get the combined packages with maximum weight
        $combinedPackages = $this->deliveryTimeCalculator->maxLoadCombination($data, $base['max_weight']);

        // Calculate combined packages
        $combinedPackages = $this->deliveryTimeCalculator->calculateDeliveryForCombinedPackages($combinedPackages, $data, $result, $vehicles, $base);

        // Sort packages by weight in descending order
        $remainingPackages = collect($data)->whereNotIn('name', collect($combinedPackages)->pluck('name')->toArray())->sortByDesc('weight')->values();

        // If there is no more combined packages, then calculate each package individually
        // else recursively calculate for combined packages
        if ($combinedPackages == []) {
            // Calculate remaining packages delivered individually
            $this->deliveryTimeCalculator->calculateDeliveryForSinglePackage($remainingPackages, $result, $vehicles, $base);
        } else {
            // Calculate remaining combined packages delivery
            $this->calculateDeliveryTime($remainingPackages->toArray(), $result, $vehicles, $base);
        }
    }
}
