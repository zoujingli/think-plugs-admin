<?php

// +----------------------------------------------------------------------
// | Admin Plugin for ThinkAdmin
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
use think\admin\extend\CodeExtend;
use think\admin\extend\ToolsExtend;

/**
 * ComposerPlugin
 * Class Plugin
 * @package app\admin
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var Composer
     */
    protected $composer;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $manager = $composer->getRepositoryManager();
        $manager->prependRepository($manager->createRepository('composer', [
            'url' => CodeExtend::deSafe64('aHR0cHM6Ly9vcGVuLmN1Y2kuY2MvcGx1Z2lu')
        ]));
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
            'pre-autoload-dump'  => [
                ['preAutoloadDump', 0],
            ],
            'post-autoload-dump' => [
                ['postAutoloadDump', 0],
            ],
        ];
    }

    public function preAutoloadDump()
    {
        $root = dirname($this->composer->getConfig()->get('vendor-dir'));
        $json = json_decode(file_get_contents("{$root}/composer.json"), true);
        if (empty($json['type']) && empty($json['name'])) {
            $type = 'project';
        } else {
            $type = $this->composer->getPackage()->getType();
        }

        if ($type === 'project') {

            // 注册自动加载规则
            $auto = $this->composer->getPackage()->getAutoload();
            if (empty($auto)) $this->composer->getPackage()->setAutoload([
                'psr-0' => ['' => 'extend'], 'psr-4' => ['app\\' => 'app'],
            ]);

            // 初始化指令入口 ( 后面需要执行安装指令 )
            if (!file_exists($file = "{$root}/think")) {
                copy(dirname(__DIR__) . '/stc/sysroot/think', $file);
            }

            // 初始化配置文件 ( 没有的时候会报错无法执行安装 )
            ToolsExtend::copyfile(dirname(__DIR__) . '/stc/config', "{$root}/config", [], false, false);

            // 初始化应用入口
            if (!file_exists($file = "{$root}/app/index/controller/Index.php")) {
                (file_exists(dirname($file)) or mkdir(dirname($file), 0755, true)) && file_put_contents($file, '<?php'
                    . "\n\nnamespace app\index\controller;"
                    . "\n\nclass Index extends \\think\\admin\\Controller\n{"
                    . "\n\tpublic function index() {\n\t\t\$this->redirect(sysuri('admin/login/index'));\n\t}"
                    . "\n}\n");
            }

            // 自动注册执行指令
            $dispatcher = $this->composer->getEventDispatcher();
            $postScripts = $json['scripts']['post-autoload-dump'] ?? [];
            if (!in_array($script = '@php think service:discover', $postScripts)) {
                $dispatcher->addListener('post-autoload-dump', $script);
            }
            if (!in_array($script = '@php think xadmin:publish', $postScripts)) {
                $dispatcher->addListener('post-autoload-dump', $script);
            }
        }
    }

    public function postAutoloadDump()
    {
    }
}