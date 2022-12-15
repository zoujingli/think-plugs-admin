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
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Plugin\PluginInterface;
use think\admin\extend\CodeExtend;
use think\admin\extend\ToolsExtend;

/**
 * 组件插件注册
 * Class Install
 * @package app\admin
 */
class Install implements PluginInterface
{
    /**
     * @param \Composer\Composer $composer
     * @param \Composer\IO\IOInterface $io
     * @throws \Seld\JsonLint\ParsingException
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        // 检测配置状态
        $rootJson = (new JsonFile('composer.json'))->read();
        $pluginUrl = CodeExtend::deSafe64('aHR0cHM6Ly9vcGVuLmN1Y2kuY2MvcGx1Z2lu');
//      foreach ($rootJson['repositories'] ?? [] as $item) if (empty($pluginCenter) && isset($item['url'])) {
//          if (is_numeric(strpos($item['url'], $pluginUrl))) $pluginCenter = true;
//      }

        // 临时动态注册
        $manager = $composer->getRepositoryManager();
        $manager->prependRepository($manager->createRepository('composer', [
            'url' => $pluginUrl, 'canonical' => false,
        ]));

        // 如果项目类型配置
        if ($composer->getPackage()->getType() === 'project' || empty($rootJson['type']) && empty($rootJson['name'])) {

            // 动态修改项目配置
//          if (empty($pluginCenter)) {
//              $composer->getConfig()->getConfigSource()->addRepository('plugins', [
//                  'url' => $pluginUrl, 'type' => 'composer', 'canonical' => false
//              ]);
//          }

            // 注册自动加载规则
            $auto = $composer->getPackage()->getAutoload();
            if (empty($auto)) $composer->getPackage()->setAutoload([
                'psr-0' => ['' => 'extend'], 'psr-4' => ['app\\' => 'app'],
            ]);


            // 初始化指令入口 ( 后面需要执行安装指令 )
            if (!file_exists($file = 'think')) {
                copy(dirname(__DIR__) . '/stc/sysroot/think', $file);
            }

            // 初始化配置文件 ( 没有的时候会报错无法执行安装 )
            ToolsExtend::copyfile(dirname(__DIR__) . '/stc/config', 'config', [], false, false);

            // 初始化应用入口 （ 主要是用来跳转到后台管理入口 ）
            if (!file_exists($file = 'app/index/controller/Index.php')) {
                if (file_exists(dirname($file)) || mkdir(dirname($file), 0755, true)) file_put_contents($file,
                    '<?php' . "\n\nnamespace app\index\controller;\n\nclass Index extends \\think\\admin\\Controller\n{"
                    . "\n\tpublic function index()\n\t{\n\t\t\$this->redirect(sysuri('admin/login/index'));\n\t}\n}\n");
            }

            // 自动注册执行指令
            $event = $composer->getEventDispatcher();
            $scripts = (array)($rootJson['scripts']['post-autoload-dump'] ?? []);
            if (!in_array($script = '@php think xadmin:publish', $scripts)) {
                $event->addListener('post-autoload-dump', function () use ($composer) {
                    [$state, $scripts] = array_values(static::syncService());
                    if ($state && count($scripts) > 0) {
                        $dispatcher = $composer->getEventDispatcher();
                        foreach ($scripts as $script) {
                            $dispatcher->addListener('PluginScript', $script);
                        }
                        $dispatcher->dispatch('PluginScript');
                    }
                });
                $event->addListener('post-autoload-dump', $script);
            }
        }
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    /**
     * 同步服务配置
     * @return false|int
     */
    public static function syncService(): array
    {
        [$scripts, $services] = [[], []];
        if (file_exists($file = 'vendor/composer/installed.json')) {
            $packages = json_decode(@file_get_contents($file), true);
            if (isset($packages['packages'])) $packages = $packages['packages'];
            foreach ($packages as $package) {
                if (!empty($package['extra']['plugin']['scripts'])) {
                    $scripts = array_merge($scripts, (array)$package['extra']['plugin']['scripts']);
                }
                if (!empty($package['extra']['think']['services'])) {
                    $services = array_merge($services, (array)$package['extra']['think']['services']);
                }
            }
        }
        $header = "// Automatically Generated At: " . date('Y-m-d H:i:s') . PHP_EOL . 'declare(strict_types=1);';
        $content = '<?php' . PHP_EOL . $header . PHP_EOL . 'return ' . var_export(array_unique($services), true) . ';';
        return ['state' => file_put_contents('vendor/services.php', $content), 'scripts' => array_unique($scripts)];
    }
}