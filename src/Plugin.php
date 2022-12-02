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
use think\admin\extend\ToolsExtend;

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

    /**
     * 注册订阅事件
     * @return \array[][]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'post-autoload-dump' => [
                ['postAutoloadDump', 0],
            ],
        ];
    }

    /**
     * PostAutoloadDump 事件处理
     * @param \Composer\Script\Event $event
     * @return void
     */
    public function postAutoloadDump(Event $event)
    {

        // 获取当前环境变量
        $root = dirname($event->getComposer()->getConfig()->get('vendor-dir'));

        // 初始化指令入口文件，方便后面执行安装指令
        if (!file_exists($file = "{$root}/think")) {
            copy(dirname(__DIR__) . '/stc/sysroot/think', $file);
        }

        // 初始化系统配置文件 ( 没有时候会报错无法执行安装 )
        ToolsExtend::copyfile(dirname(__DIR__) . '/stc/config', "{$root}/config", [], false, false);

        // 初始化应用入口应用
        if (!file_exists($file = "{$root}/app/index/controller/Index.php")) {
            file_exists(dirname($file)) or mkdir(dirname($file), 0755, true);
            file_put_contents($file, '<?php'
                . "\n\nnamespace app\index\controller;"
                . "\n\nclass Index extends \\think\\admin\\Controller {"
                . "\n\tpublic function index() {\n\t\t\$this->redirect(sysuri('admin/login/index'));\n\t}"
                . "\n}\n");
        }

        // 注册初始化指令并执行安装相关指令
        $dispatcher = $event->getComposer()->getEventDispatcher();
        $dispatcher->addListener('post-think-admin', '@php think service:discover');
        $dispatcher->addListener('post-think-admin', '@php think xadmin:publish');
        $dispatcher->dispatch('post-think-admin');
    }
}