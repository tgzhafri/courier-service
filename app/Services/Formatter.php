<?php

namespace App\Services;

class Formatter
{
    public function formatString($data, $discount, $total): string
    {
        $name = $data['name'];
        return "$name $discount $total";
    }

    public function formatArray($data, $discount, $total): array
    {
        return [
            'name' => $data['name'],
            'discount' => $discount,
            'total' => $total
        ];
    }

    public function stringToArray(string $string): array
    {
        // Split the string into lines
        $lines = explode("\n", $string);

        // Loop through the lines and split each line into individual values
        $array = [];
        foreach ($lines as $key => $line) {
            if (!empty($line)) {
                $values = explode(" ", $line);

                if ($key == 0) {
                    $array['base'] = array_values($values);
                } else {
                    // Remove any empty values
                    $values = array_filter($values, function ($value) {
                        return !empty($value) && $value != 'null';
                    });

                    // Add the values to the array
                    $array['package'][] = array_values($values);
                }
            }
        }
        return $array;
    }
}
