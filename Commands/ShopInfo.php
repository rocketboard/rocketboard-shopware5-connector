<?php

namespace RocketBoard\Commands;

use Shopware\Components\Model\ModelManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Shopware\Commands\ShopwareCommand;

class ShopInfo extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('Rocket:GROUP:OPERATION')
            ->setDescription('Description of this command')
            ->addArgument(
                'my-argument',
                InputArgument::REQUIRED,
                'An required argument (positional)'
            )
            ->addOption(
                'my-option',
                null,
                InputOption::VALUE_OPTIONAL,
                'An optional *option*',
                'My-Default-Value'
            )
            ->setHelp(<<<EOF
The <info>%command.name%</info> implements a command.
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($input->getOption('my-option'));
        $output->writeln($input->getArgument('my-argument'));

    }
}
