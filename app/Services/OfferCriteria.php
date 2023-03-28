<?php

namespace App\Services;

class OfferCriteria
{
    protected $offerFilePath = "database/data/offer.json";
    private $dataReader;

    public function __construct(DataReader $dataReader,)
    {
        $this->dataReader = $dataReader;
    }

    public function getOffer($item)
    {
        $offer = collect($this->dataReader->readData([], $this->offerFilePath));

        return $offer->firstWhere('code', $item['offer_code']);
    }
}
