<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2022 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// | 免费声明 ( https://thinkadmin.top/disclaimer )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/think-plugs-admin
// +----------------------------------------------------------------------

namespace app\admin;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;

/**
 * Composer Plugin
 * Class Plugin
 * @package app\admin
 */
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

        // 获取当前环境变量
        $composer = $event->getComposer();
        $root = dirname($composer->getConfig()->get('vendor-dir'));

        // 初始化指令入口文件，方便后面执行安装指令
        if (!file_exists($file = "{$root}/think")) {
            copy(dirname(__DIR__) . '/stc/sysroot/think', $file);
        }

        // 初始化系统缓存配置文件，没有时候会报错，无法执行安装
        if (!file_exists($file = "{$root}/config/cache.php")) {
            file_exists(dirname($file)) or mkdir(dirname($file));
            copy(dirname(__DIR__) . '/stc/config/cache.php', $file);
        }

        // 注册指令并执行安装指令
        $dispatcher = $composer->getEventDispatcher();
        foreach (['service:discover', 'vendor:publish', 'xadmin:publish'] as $command) {
            $dispatcher->addListener('post-thinkadmin-dump', "@php think {$command}");
        }
        $dispatcher->dispatch('post-thinkadmin-dump');
    }
}