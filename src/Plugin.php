<?php

namespace app\admin;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'post-autoload-dump' => [
                ['postAutoloadDump', 0],
            ],
        ];
    }

    public function postAutoloadDump(Event $event)
    {
        $root = dirname($event->getComposer()->getConfig()->get('vendor-dir'));
        // 初始化指令入口文件
        if (!file_exists($file = "{$root}/think")) {
            copy(__DIR__ . '/stubs/think.stub', $file);
        }
        // 初始化系统缓存配置文件
        if (!file_exists($file = "{$root}/config/cache.php")) {
            file_exists(dirname($file)) or mkdir(dirname($file));
            copy(__DIR__ . '/stubs/cache.stub', $file);
        }
        $event->getComposer()->getEventDispatcher()->dispatchScript('@php think xadmin:publish');
    }
}