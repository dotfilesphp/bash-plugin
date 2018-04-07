<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Plugins\Bash;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Plugin;
use Dotfiles\Plugins\Bash\Config\Definition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class BashPlugin extends Plugin
{
    public function getName()
    {
        return 'bash';
    }

    public function setupConfiguration(Config $config)
    {
        $config->addDefinition(new Definition());
    }

    public function configureContainer(ContainerBuilder $container, Config $config)
    {
        $locator = new FileLocator(__DIR__.'/Resources');
        $loader = new YamlFileLoader($container, $locator);
        $loader->load('services.yaml');
    }
}
