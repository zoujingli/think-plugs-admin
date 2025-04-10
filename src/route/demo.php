<?php

// +----------------------------------------------------------------------
// | Admin Plugin for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2025 ThinkAdmin [ thinkadmin.top ]
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// | 免责声明 ( https://thinkadmin.top/disclaimer )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/think-plugs-admin
// | github 代码仓库：https://github.com/zoujingli/think-plugs-admin
// +----------------------------------------------------------------------

use think\admin\Library;
use think\admin\service\RuntimeService;

/*! 演示环境禁止操作路由绑定 */
if (RuntimeService::check('demo')) {
    Library::$sapp->route->post('index/pass', static function () {
        return json(['code' => 0, 'info' => lang('演示环境禁止修改用户密码！')]);
    });
    Library::$sapp->route->post('config/system', static function () {
        return json(['code' => 0, 'info' => lang('演示环境禁止修改系统配置！')]);
    });
    Library::$sapp->route->post('config/storage', static function () {
        return json(['code' => 0, 'info' => lang('演示环境禁止修改系统配置！')]);
    });
    Library::$sapp->route->post('menu', static function () {
        return json(['code' => 0, 'info' => lang('演示环境禁止给菜单排序！')]);
    });
    Library::$sapp->route->post('menu/index', static function () {
        return json(['code' => 0, 'info' => lang('演示环境禁止给菜单排序！')]);
    });
    Library::$sapp->route->post('menu/add', static function () {
        return json(['code' => 0, 'info' => lang('演示环境禁止添加菜单！')]);
    });
    Library::$sapp->route->post('menu/edit', static function () {
        return json(['code' => 0, 'info' => lang('演示环境禁止编辑菜单！')]);
    });
    Library::$sapp->route->post('menu/state', static function () {
        return json(['code' => 0, 'info' => lang('演示环境禁止禁用菜单！')]);
    });
    Library::$sapp->route->post('menu/remove', static function () {
        return json(['code' => 0, 'info' => lang('演示环境禁止删除菜单！')]);
    });
    Library::$sapp->route->post('user/pass', static function () {
        return json(['code' => 0, 'info' => lang('演示环境禁止修改密码！')]);
    });
}