<?php

namespace App\Command;

use App\Service\RateManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExchangeGetRatesCommand extends Command
{
    protected static $defaultName = 'exchange:get-rates';

    private $manager;

    public function __construct(RateManager $manager)
    {
        $this->manager = $manager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Get exchange rates from provider.')
            ->addArgument('provider', InputArgument::OPTIONAL, 'Provider name to fetch data from.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $provider = $input->getArgument('provider');
        $notQuiet = !$input->getOption('quiet');
        $notQuiet && $io->note(sprintf('Update rates for %s provider.', $provider ?? 'default'));

        try {
            $this->manager->updateRates($this->manager->getProvider($provider));
            $notQuiet && $io->success('Rates was updated successfully.');
        } catch (\InvalidArgumentException $e) {
            $io->error('Error: '.$e->getMessage());
            return 1;
        }
    }
}
