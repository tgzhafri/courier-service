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
            collect($array['package'])->each(function ($item) use (&$data) {
                return $data['package'][] = array_combine(['name', 'weight', 'distance', 'offer_code'], $item);
            });
        }

        return $data;
    }
}
