<?php

namespace app\admin;

use think\admin\service\PluginService;

/**
 * 组件注册服务
 * Class Service
 * @package app\admin
 */
class Service extends PluginService
{
    /**
     * 服务启动注册
     * @return void
     */
    public function boot(): void
    {
        // 定义安装复制目录
        $this->copyPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'stc';
        parent::boot();
    }
}