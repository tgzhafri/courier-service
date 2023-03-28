<?php

namespace App\Services;

class InputValidator
{
    public function validate(array $input): bool
    {
        if (empty($input)) {
            return true;
        }

        // check if data formatted correctly
        if (!isset($input['base']) || !isset($input['package'])) {
            return false;
        }

        // validate base input
        $validateBase = $this->baseRules($input['base']);
        if (!$validateBase) {
            return false;
        }

        //validate package input
        foreach ($input['package'] as $item) {
            $validated =  $this->packageRules($item);
            if (!$validated) {
                return false;
            }
        }

        return true;
    }

    public function packageRules(array $input): bool
    {
        if (count($input) != 4) {
            return false;
        }
        $name = $input[0];
        $weight = $input[1];
        $distance = $input[2];
        $offerCode = $input[3];

        if (
            is_string($name)
            && is_numeric($weight)
            && is_numeric($distance)
            && is_string($offerCode)
            && !empty($name)
            && !empty($weight)
            && !empty($distance)
            && !empty($offerCode)
        ) {
            return true;
        }
        return false;
    }

    public function baseRules($input): bool
    {
        if (count($input) != 2) {
            return false;
        }

        $baseDeliveryCost = $input[0];
        $numOfPackages = $input[1];

        if (!is_numeric($baseDeliveryCost) || !is_numeric($numOfPackages)) {
            return false;
        }
        return true;
    }
}
