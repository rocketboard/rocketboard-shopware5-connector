<?php

namespace RocketBoard;

use Shopware\Components\Plugin;
use Shopware\Components\Console\Application;
use RocketBoard\Commands\ShopInfo;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Shopware-Plugin RocketBoard.
 */
class RocketBoard extends Plugin
{

    /**
    * @param ContainerBuilder $container
    */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('rocket_board.plugin_dir', $this->getPath());
        parent::build($container);
    }

}
