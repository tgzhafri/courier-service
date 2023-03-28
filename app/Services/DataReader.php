<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class DataReader
{
    /**
     * Read data from the input file
     *
     * @return Collection
     */
    public function readData(array $array = [], string|null $path = null): array
    {
        $data = [];

        if (empty($array)) {
            $dataJson = File::get($path);
            $data = json_decode($dataJson, true);
        } else {
            $data['base'] = array_combine(['base_delivery_cost', 'no_of_packages',], $array['base']);
            collect($array['packages'])->each(function ($item) use (&$data) {
                return $data['packages'][] = array_combine(['name', 'weight', 'distance', 'offer_code'], $item);
            });
            if (isset($array['vehicle'])) {
                $data['vehicle'] = array_combine(['no_of_vehicles', 'max_speed', 'max_weight'], $array['vehicle']);
            }
        }

        return $data;
    }
}
