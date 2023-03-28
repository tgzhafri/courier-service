<?php

namespace App\Services;

class DiscountCalculator
{
    private $offerCriteria;

    public function __construct(OfferCriteria $offerCriteria)
    {
        $this->offerCriteria = $offerCriteria;
    }

    /**
     * Calculate the applicable discount for an item.
     *
     * @param $item
     * @param float $totalCost
     * @return float
     */
    public function calculate($item, float $cost): float
    {
        $discount = 0.0;

        $offer = $this->offerCriteria->getOffer($item);

        // Check if the item meets the offer criteria
        if (
            $offer
            && $offer['max_distance'] >= $item['distance']
            && $offer['min_distance'] <= $item['distance']
            && $offer['max_weight'] >= $item['weight']
            && $offer['min_weight'] <= $item['weight']
        ) {
            // Calculate discount percentage based on offer's discount value
            $discountPercentage = $offer['discount'] / 100.0;

            // Calculate the discount amount
            $discount = $cost * $discountPercentage;
        }

        return $discount;
    }
}
