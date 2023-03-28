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

    public function costInputToArray(string $string): array
    {
        // Split the string into lines
        $lines = explode("\n", $string);

        // Loop through the lines and split each line into individual values
        $array = [];

        foreach ($lines as $index => $line) {
            if (!empty($line)) {
                $values = explode(" ", $line);

                if ($index == 0) {
                    // separate Base Input (first index)
                    $array['base'] = array_values($values);
                } else {
                    // Remove any empty values
                    $values = array_filter($values, function ($value) {
                        return !empty($value) && $value != 'null';
                    });

                    // Add the values to the array
                    $array['packages'][] = array_values($values);
                }
            }
        }
        return $array;
    }

    public function timeInputToArray(string $string): array
    {
        // Split the string into lines
        $lines = explode("\n", $string);

        // Loop through the lines and split each line into individual values
        $array = [];
        foreach ($lines as $index => $line) {
            if (empty($line)) {
                break;
            }
            // remove white space front and back of the string before explode into array
            $values = explode(" ", trim($line));

            if ($index == 0) {
                // separate Base Input (first index)
                $array['base'] = array_values($values);
            } elseif ($index + 1 == count($lines)) {
                // separate Vehicle Input (last index)
                $array['vehicle'] = array_values($values);
            } else {
                // Remove any empty values
                $values = array_filter($values, function ($value) {
                    return !empty($value) && $value != 'null';
                });
                // Add the values to the array
                $array['packages'][] = array_values($values);
            }
        }
        return $array;
    }
}
