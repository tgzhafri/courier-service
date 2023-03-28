<?php

namespace App\Console\Commands;

use App\Services\CostCalculator;
use App\Services\DataReader;
use App\Services\DiscountCalculator;
use App\Services\InputValidator;
use App\Services\Formatter;
use Illuminate\Console\Command;

class CostEstimationCommand extends Command
{
    protected $inputFilePath = "database/data/input_C1.json";

    private $inputValidator;
    private $dataReader;
    private $costCalculator;
    private $discountCalculator;
    private $formatter;

    public function __construct(
        InputValidator $inputValidator,
        DataReader $dataReader,
        CostCalculator $costCalculator,
        DiscountCalculator $discountCalculator,
        Formatter $formatter
    ) {
        parent::__construct();
        $this->inputValidator = $inputValidator;
        $this->dataReader = $dataReader;
        $this->costCalculator = $costCalculator;
        $this->discountCalculator = $discountCalculator;
        $this->formatter = $formatter;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courier:cost-estimate {input?*} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Coding challenge 1 for courier service to get delivery cost estimation with offer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->comment('Courier service Challenge 1 --started--');

        $input = $this->argument('input');
        if (empty($input)) {
            return $this->error('Invalid input');
        }

        $formatted = $this->formatter->costInputToArray($input[0]);

        if (!$this->inputValidator->validate($formatted)) {
            return $this->error('Invalid input');
        }

        $data = $this->dataReader->readData($formatted, $this->inputFilePath);

        if (empty($data)) {
            return $this->error('Data is empty');
        }

        $results = array();

        foreach ($data['packages'] ?? $data as $item) {
            $cost = $this->costCalculator->calculate($item, $data['base'] ?? null);

            $discount = $this->discountCalculator->calculate($item, $cost);

            $total = $cost - $discount;

            $this->info($item['name'] . " $discount $total");

            $results[] = $this->formatter->formatArray($item, $discount, $total);
        }
        $this->table(['Name', 'Discount', 'Total'], $results);

        $this->comment('Courier service Challenge 1 --finished--');
    }
}
