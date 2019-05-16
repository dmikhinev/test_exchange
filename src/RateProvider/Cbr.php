<?php

namespace App\RateProvider;

use App\Entity\Rate;

class Cbr implements RateProviderInterface
{
    private const NAME = 'cbr';
    private const URL = 'https://www.cbr.ru/scripts/XML_daily.asp';
    private const BASE = 'RUB';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getRates(): array
    {
        $xml = file_get_contents(self::URL);
        $xml = simplexml_load_string($xml);
        $result = [];

        foreach ($xml->Valute as $item) {
            $rate = (new Rate())
                ->setSource(self::NAME)
                ->setBaseCurrency(self::BASE)
                ->setCurrency((string)$item->CharCode)
                ->setValue((float)str_replace(',', '.', (string)$item->Value) / (int)$item->Nominal)
            ;
            $result[] = $rate;
        }

        return $result;
    }

}
