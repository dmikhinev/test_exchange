<?php

namespace App\RateProvider;

use App\Entity\Rate;

interface RateProviderInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return Rate[]
     */
    public function getRates(): array;
}
