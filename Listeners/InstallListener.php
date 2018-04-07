<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Plugins\Bash\Listeners;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Event\InstallEvent;
use Dotfiles\Plugins\Bash\Event\ReloadBashConfigEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InstallListener implements EventSubscriberInterface
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var Config
     */
    private $config;

    private $installDir;

    private $logger;

    public function __construct(Dispatcher $dispatcher, Config $config, LoggerInterface $logger)
    {
        $this->dispatcher = $dispatcher;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param mixed $installDir
     */
    public function setInstallDir($installDir): void
    {
        $this->installDir = $installDir;
    }

    public static function getSubscribedEvents()
    {
        return array(
            InstallEvent::NAME => 'onInstallEvent',
        );
    }

    public function onInstallEvent(InstallEvent $event)
    {
        $reloadEvent = new ReloadBashConfigEvent($this->logger);
        $this->dispatcher->dispatch(ReloadBashConfigEvent::NAME, $reloadEvent);
        $this->generateDotfilesConfig($reloadEvent->getBashConfig());

        $installDir = $this->config->get('dotfiles.install_dir');
        $target = $this->config->get('dotfiles.home_dir').'/.bashrc';
        $event->addPatch($target, "source \"${installDir}/bashrc\"");
    }

    private function generateDotfilesConfig($bashConfig)
    {
        $installDir = $this->installDir;

        $uname = php_uname();
        if (false !== strpos('darwin', $uname)) {
            $fileName = 'bash_profile';
        } else {
            $fileName = 'bashrc';
        }

        // write config into dotfiles location
        $contents = <<<EOC
# WARNING!!!
# This file is generated automatically by DOTFILES installer
# All changes in this file will be overwrite later with DOTFILES

export PATH="{$installDir}/bin:\$PATH"
$bashConfig

# END DOTFILES CONFIG

EOC;

        file_put_contents($installDir.DIRECTORY_SEPARATOR.$fileName, $contents, LOCK_EX);
    }
}
