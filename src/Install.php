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
use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Repository\InstalledRepositoryInterface;
use think\admin\extend\CodeExtend;

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
        $composer->getInstallationManager()->addInstaller(new class($io, $composer) extends LibraryInstaller {

            public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
            {
                parent::install($repo, $package);
                $this->copyStaticFiles($package);
            }

            public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
            {
                parent::update($repo, $initial, $target);
                $this->copyStaticFiles($target);
            }

            public function getInstallPath(PackageInterface $package): string
            {
                if ($this->composer->getPackage()->getType() === 'project') {
                    $extra = $package->getExtra();
                    if (!empty($extra['plugin']['path'])) {
                        return $extra['plugin']['path'];
                    }
                }
                return parent::getInstallPath($package);
            }

            protected function copyStaticFiles(PackageInterface $package)
            {
                if ($this->composer->getPackage()->getType() === 'project') {
                    $extra = $package->getExtra();
                    $install = $this->getInstallPath($package);
                    if (!empty($extra['plugin']['copy'])) {
                        foreach ((array)$extra['plugin']['copy'] as $source => $target) {
                            $this->io->write("<info>Copy Path {$source} to {$target}</info>");
                            if (file_exists($file = $install . DIRECTORY_SEPARATOR . $source)) {
                                file_exists(dirname($target)) || mkdir(dirname($target), 0755, true);
                                $this->filesystem->copy($file, $target);
                            }
                        }
                    }
                    if (!empty($extra['plugin']['init'])) {
                        foreach ((array)$extra['plugin']['init'] as $source => $target) {
                            $this->io->write("<info>Init File {$source} to {$target}</info>");
                            if (file_exists($target) && is_file($target)) {
                                $this->io->write("<info>Init File {$target} exist!</info>");
                            } elseif (file_exists($file = $install . DIRECTORY_SEPARATOR . $source)) {
                                file_exists(dirname($target)) || mkdir(dirname($target), 0755, true);
                                copy($file, $target);
                            }
                        }
                    }
                    if (!empty($extra['plugin']['clear'])) {
                        $this->io->write("<info>Remove {$install}</info>");
                        $this->filesystem->removeDirectory($install);
                    }
                }
            }

            public function supports(string $packageType): bool
            {
                return 'think-admin-plugin' === $packageType;
            }
        });

        // 动态注册仓库源
        $manager = $composer->getRepositoryManager();
        $manager->prependRepository($manager->createRepository('composer', [
            'url' => CodeExtend::deSafe64('aHR0cHM6Ly9vcGVuLmN1Y2kuY2MvcGx1Z2lu'), 'canonical' => false,
        ]));

        // 如果项目类型配置
        $rootJson = (new JsonFile('composer.json'))->read();
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

            // 注册插件安装
            $dispatcher = $composer->getEventDispatcher();
            $dispatcher->addListener('post-autoload-dump', function () use ($rootJson, $dispatcher) {

                // 注册插件脚本
                $ignore = '--ignore-platform-req=CallNotPluginPublish';
                [$state, $scripts] = array_values(static::toServices());
                if ($state && count($scripts) > 0) foreach ($scripts as $script) {
                    if (is_numeric(stripos($script, 'composer'))) $script .= " {$ignore}";
                    $isPlugin = true;
                    $dispatcher->addListener('PluginScript', $script);
                }

                // 注册安装脚本
                global $argv;
                $scripts = $rootJson['scripts']['post-autoload-dump'] ?? [];
                if (!in_array($ignore, $argv) && !in_array('@php think xadmin:publish', $scripts)) {
                    $isPlugin = true;
                    $dispatcher->addListener('PluginScript', '@php think xadmin:publish');
                }

                // 执行插件脚本
                isset($isPlugin) && $dispatcher->dispatch('PluginScript');
            });
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
    private static function toServices(): array
    {
        [$scripts, $services, $versions] = [[], [], []];
        if (file_exists($file = 'vendor/composer/installed.json')) {
            $packages = json_decode(@file_get_contents($file), true);
            foreach ($packages['packages'] ?? $packages as $package) {
                $versions[$package['name']] = $package['version'];
                if (!empty($package['extra']['plugin']['scripts'])) {
                    $scripts = array_merge($scripts, (array)$package['extra']['plugin']['scripts']);
                }
                if (!empty($package['extra']['think']['services'])) {
                    $services = array_merge($services, (array)$package['extra']['think']['services']);
                }
            }
        }

        // 动态配置服务
        $header = '// Automatically Generated At: ' . date('Y-m-d H:i:s') . PHP_EOL . 'declare(strict_types=1);';
        $content = '<?php' . PHP_EOL . $header . PHP_EOL . 'return ' . var_export($services, true) . ';';
        file_put_contents('vendor/services.php', $content);

        // 组件安装信息
        $content = '<?php' . PHP_EOL . $header . PHP_EOL . 'return ' . var_export($versions, true) . ';';
        file_put_contents('vendor/versions.php', $content);

        return ['state' => true, 'scripts' => array_unique($scripts)];
    }
}