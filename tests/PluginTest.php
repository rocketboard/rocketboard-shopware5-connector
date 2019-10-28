<?php

namespace RocketBoard\Tests;

use RocketBoard\RocketBoard as Plugin;
use Shopware\Components\Test\Plugin\TestCase;

class PluginTest extends TestCase
{
    protected static $ensureLoadedPlugins = [
        'RocketBoard' => []
    ];

    public function testCanCreateInstance()
    {
        /** @var Plugin $plugin */
        $plugin = Shopware()->Container()->get('kernel')->getPlugins()['RocketBoard'];

        $this->assertInstanceOf(Plugin::class, $plugin);
    }
}
