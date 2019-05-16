<?php

namespace App\Repository;

use App\Entity\Rate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Rate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rate[]    findAll()
 * @method Rate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Rate::class);
    }

    /**
     * @param string $currencyFrom
     * @param string $currencyTo
     * @return Rate[]
     */
    public function findCrossRates(string $currencyFrom, string $currencyTo)
    {
        $qb = $this->createQueryBuilder('r');
        $qb
            ->addSelect('rc')
            ->join(Rate::class, 'rc', Join::WITH, 'rc.baseCurrency = r.baseCurrency AND rc.source = r.source')
            ->where('r.currency = :currencyFrom')
            ->andWhere('rc.currency = :currencyTo')
            ->setParameters([
                'currencyFrom' => $currencyFrom,
                'currencyTo' => $currencyTo
            ])
            ->orderBy('r.updatedAt', 'DESC')
            ->setMaxResults(1)
        ;
        /** @var Rate[] $r */
        $r = $qb->getQuery()->getResult();
        $result = [];

        foreach ($r as $rate) {
            if ($rate->getCurrency() === $currencyFrom) {
                $result['from'] = $rate;
            } else {
                $result['to'] = $rate;
            }
        }

        return $result;
    }
}
