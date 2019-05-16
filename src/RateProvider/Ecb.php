<?php

namespace App\RateProvider;

use App\Entity\Rate;

class Ecb implements RateProviderInterface
{
    private const NAME = 'ecb';
    private const URL = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
    private const BASE = 'EUR';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getRates(): array
    {
        $xml = file_get_contents(self::URL);
        $xml = simplexml_load_string($xml);
        $result = [];

        foreach ($xml->Cube->Cube->Cube as $item) {
            $rate = (new Rate())
                ->setSource(self::NAME)
                ->setBaseCurrency(self::BASE)
                ->setCurrency((string)$item['currency'])
                ->setValue(1 / (float)$item['rate'])
            ;
            $result[] = $rate;
        }

        return $result;
    }

}
