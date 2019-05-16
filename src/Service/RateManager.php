<?php

namespace App\Service;

use App\Entity\Rate;
use App\RateProvider\RateProviderInterface;
use Doctrine\ORM\EntityManagerInterface;

class RateManager
{
    /**
     * @var RateProviderInterface[]
     */
    private $providers = [];

    /**
     * @var string
     */
    private $defaultProvider;

    private $em;

    public function __construct(string $defaultProvider, EntityManagerInterface $em)
    {
        $this->defaultProvider = $defaultProvider;
        $this->em = $em;
    }

    public function addProvider(RateProviderInterface $provider): self
    {
        if (array_key_exists($provider->getName(), $this->providers)) {
            throw new \InvalidArgumentException(sprintf('Provider "%s" already added as "%s"', $provider->getName(), get_class($this->providers[$provider->getName()])));
        }
        $this->providers[$provider->getName()] = $provider;

        return $this;
    }

    public function getProvider(?string $name = null): RateProviderInterface
    {
        if (null === $name) {
            return $this->getProvider($this->defaultProvider);
        }
        if (!array_key_exists($name, $this->providers)) {
            throw new \InvalidArgumentException(sprintf('Provider "%s" was not defined', $name));
        }

        return $this->providers[$name];
    }

    public function updateRates(RateProviderInterface $provider): void
    {
        $repo = $this->em->getRepository(Rate::class);

        foreach ($provider->getRates() as $rate) {
            $exists = $repo->findOneBy(['currency' => $rate->getCurrency(), 'baseCurrency' => $rate->getBaseCurrency()]);

            if (null !== $exists) {
                $exists
                    ->setSource($rate->getSource())
                    ->setValue($rate->getValue())
                ;
            } else {
                $this->em->persist($rate);
            }
        }
        $this->em->flush();
    }

    public function convert(float $value, string $currencyFrom, string $currencyTo): float
    {
        $currencyFrom = strtoupper($currencyFrom);
        $currencyTo = strtoupper($currencyTo);
        $repo = $this->em->getRepository(Rate::class);

        $rate = $repo->findOneBy(['baseCurrency' => $currencyTo, 'currency' => $currencyFrom]);

        if (null !== $rate) {
            return $value * $rate->getValue();
        }
        $rate = $repo->findOneBy(['baseCurrency' => $currencyFrom, 'currency' => $currencyTo]);

        if (null !== $rate) {
            return $value / $rate->getValue();
        }
        $rates = $repo->findCrossRates($currencyFrom, $currencyTo);

        if (2 === count($rates)) {
            return $value * $rates['from']->getValue() / $rates['to']->getValue();
        }

        throw new \InvalidArgumentException(sprintf('Cannot convert from "%s" to "%s".', $currencyFrom, $currencyTo));
    }
}
