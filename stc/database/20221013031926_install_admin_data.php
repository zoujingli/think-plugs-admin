<?php

// +----------------------------------------------------------------------
// | Admin Plugin for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2023 ThinkAdmin [ thinkadmin.top ]
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// | 免责声明 ( https://thinkadmin.top/disclaimer )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/think-plugs-admin
// | github 代码仓库：https://github.com/zoujingli/think-plugs-admin
// +----------------------------------------------------------------------

use app\admin\Service;
use think\admin\extend\PhinxExtend;
use think\admin\model\SystemConfig;
use think\admin\model\SystemUser;
use think\migration\Migrator;

@set_time_limit(0);
@ini_set('memory_limit', -1);

/**
 * 系统模块初始化
 */
class InstallAdminData extends Migrator
{
    /**
     * 数据库初始化
     * @return void
     * @throws \think\db\exception\DbException
     */
    public function change()
    {
        $this->insertUser();
        $this->insertMenu();
        $this->insertConf();
    }

    /**
     * 初始化用户数据
     * @return void
     * @throws \think\db\exception\DbException
     */
    private function insertUser()
    {
        // 检查是否存在
        if (SystemUser::mk()->count() > 0) return;

        // 初始化默认数据
        SystemUser::mk()->save([
            'id'       => '10000',
            'username' => 'admin',
            'nickname' => '超级管理员',
            'password' => '21232f297a57a5a743894a0e4a801fc3',
            'headimg'  => 'https://thinkadmin.top/static/img/icon.png',
        ]);
    }

    /**
     * 初始化系统菜单
     * @return void
     */
    private function insertMenu()
    {
        // 初始化菜单数据
        PhinxExtend::write2menu([
            [
                'name' => '系统管理',
                'sort' => '100',
                'subs' => Service::menu(),
            ],
        ], [
            'url|node' => 'admin/config/index'
        ]);
    }

    /**
     * 初始化配置参数
     * @return void
     * @throws \think\db\exception\DbException
     */
    private function insertConf()
    {
        // 检查数据
        if (SystemConfig::mk()->count()) return;

        // 写入数据
        SystemConfig::mk()->insertAll([
            ['type' => 'base', 'name' => 'app_name', 'value' => 'ThinkAdmin'],
            ['type' => 'base', 'name' => 'app_version', 'value' => 'v6'],
            ['type' => 'base', 'name' => 'editor', 'value' => 'ckeditor5'],
            ['type' => 'base', 'name' => 'login_name', 'value' => '系统管理'],
            ['type' => 'base', 'name' => 'site_copy', 'value' => '©版权所有 2014-' . date('Y') . ' ThinkAdmin.Top'],
            ['type' => 'base', 'name' => 'site_icon', 'value' => 'https://v6.thinkadmin.top/upload/4b/5a423974e447d5502023f553ed370f.png'],
            ['type' => 'base', 'name' => 'site_name', 'value' => 'ThinkAdmin'],
            ['type' => 'base', 'name' => 'site_theme', 'value' => 'default'],
            ['type' => 'storage', 'name' => 'allow_exts', 'value' => 'doc,gif,ico,jpg,mp3,mp4,p12,pem,png,zip,rar,xls,xlsx'],
            ['type' => 'storage', 'name' => 'type', 'value' => 'local'],
            ['type' => 'wechat', 'name' => 'type', 'value' => 'api'],
        ]);
    }
}
