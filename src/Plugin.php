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
use think\admin\extend\ToolsExtend;

/**
 * Composer Plugin
 * Class Plugin
 * @package app\admin
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * 应用根目录
     * @var string
     */
    protected $root;

    /**
     * 当前应用类型
     * @var string
     */
    protected $type;

    /**
     * 当前操作对象
     * @var Composer
     */
    protected $composer;

    public function activate(Composer $composer, IOInterface $io)
    {
        // 变量赋值
        $this->composer = $composer;
        // 项目根目录
        $this->root = dirname($composer->getConfig()->get('vendor-dir'));
        // 默认项目
        $json = json_decode(file_get_contents("{$this->root}/composer.json"), true);
        if (empty($json['type']) && empty($json['name'])) $this->type = 'project';
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

    /**
     * preAutoloadDump 事件处理
     * @return void
     */
    public function preAutoloadDump()
    {
        // 注册自动加载机制
        if ($this->type === 'project') {
            $auto = $this->composer->getPackage()->getAutoload();
            if (empty($auto)) $this->composer->getPackage()->setAutoload([
                'psr-0' => ['' => 'extend'],
                'psr-4' => ['app\\' => 'app'],
            ]);
        }
    }

    /**
     * PostAutoloadDump 事件处理
     * @return void
     */
    public function postAutoloadDump()
    {
        // 项目类型初始化安装
        if ($this->type === 'project') {

            // 初始化指令入口文件，方便后面执行安装指令
            if (!file_exists($file = "{$this->root}/think")) {
                copy(dirname(__DIR__) . '/stc/sysroot/think', $file);
            }

            // 初始化系统配置文件 ( 没有的时候会报错无法执行安装 )
            ToolsExtend::copyfile(dirname(__DIR__) . '/stc/config', "{$this->root}/config", [], false, false);

            // 初始化应用入口应用
            if (!file_exists($file = "{$this->root}/app/index/controller/Index.php")) {
                (file_exists(dirname($file)) or mkdir(dirname($file), 0755, true)) && file_put_contents($file, '<?php'
                    . "\n\nnamespace app\index\controller;"
                    . "\n\nclass Index extends \\think\\admin\\Controller {"
                    . "\n\tpublic function index() {\n\t\t\$this->redirect(sysuri('admin/login/index'));\n\t}"
                    . "\n}\n");
            }

            // 注册初始化指令并执行安装相关指令
            $dispatcher = $this->composer->getEventDispatcher();
            $dispatcher->addListener('post-think-admin', '@php think service:discover');
            $dispatcher->addListener('post-think-admin', '@php think xadmin:publish');
            $dispatcher->dispatch('post-think-admin');
        }
    }
}