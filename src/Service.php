<?php

namespace app\admin;

/**
 * 组件注册服务
 * Class Service
 * @package app\admin
 */
class Service extends \think\Service
{
    /**
     * 指定版本号
     * @var string
     */
    const VERSION = '0.0.1';

    /**
     * 配置组件服务
     * @return void
     */
    public function boot(): void
    {
        $attr = explode('\\', __NAMESPACE__);
        $addons = $this->app->config->get('app.addons', []);
        $addons[array_pop($attr)] = __DIR__ . DIRECTORY_SEPARATOR . '@' . join('\\', $attr);
        $this->app->config->set(['addons' => $addons], 'app');
    }
}