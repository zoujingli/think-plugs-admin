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

declare(strict_types=1);

namespace app\admin\controller;

use think\admin\Controller;
use think\admin\Plugin;
use think\admin\service\AdminService;
use think\admin\service\ModuleService;
use think\admin\service\RuntimeService;
use think\admin\service\SystemService;
use think\admin\Storage;
use think\admin\storage\AliossStorage;
use think\admin\storage\QiniuStorage;
use think\admin\storage\TxcosStorage;

/**
 * 系统参数配置
 * @class Config
 * @package app\admin\controller
 */
class Config extends Controller
{
    const themes = [
        'default' => '默认色0',
        'white'   => '简约白0',
        'red-1'   => '玫瑰红1',
        'blue-1'  => '深空蓝1',
        'green-1' => '小草绿1',
        'black-1' => '经典黑1',
        'red-2'   => '玫瑰红2',
        'blue-2'  => '深空蓝2',
        'green-2' => '小草绿2',
        'black-2' => '经典黑2',
    ];

    /**
     * 系统参数配置
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '系统参数配置';
        $this->files = Storage::types();
        $this->plugins = Plugin::get(null, true);
        $this->issuper = AdminService::isSuper();
        $this->systemid = ModuleService::getRunVar('uni');
        $this->framework = ModuleService::getLibrarys('topthink/framework');
        $this->thinkadmin = ModuleService::getLibrarys('zoujingli/think-library');
        if (AdminService::isSuper() && $this->app->session->get('user.password') === md5('admin')) {
            $url = url('admin/index/pass', ['id' => AdminService::getUserId()]);
            $this->showErrorMessage = lang("超级管理员账号的密码未修改，建议立即<a data-modal='%s'>修改密码</a>！", [$url]);
        }
        uasort($this->plugins, static function ($a, $b) {
            if ($a['space'] === $b['space']) return 0;
            return $a['space'] > $b['space'] ? 1 : -1;
        });
        $this->fetch();
    }

    /**
     * 修改系统参数
     * @auth true
     * @throws \think\admin\Exception
     */
    public function system()
    {
        if ($this->request->isGet()) {
            $this->title = '修改系统参数';
            $this->themes = static::themes;
            $this->fetch();
        } else {
            $post = $this->request->post();
            // 修改网站后台入口路径
            if (!empty($post['xpath'])) {
                if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $post['xpath'])) {
                    $this->error('后台入口格式错误！');
                }
                if ($post['xpath'] !== 'admin') {
                    if (is_dir(syspath("app/{$post['xpath']}")) || !empty(Plugin::get($post['xpath']))) {
                        $this->error(lang('已存在 %s 应用！', [$post['xpath']]));
                    }
                }
                RuntimeService::set(null, [$post['xpath'] => 'admin']);
            }
            // 修改网站 ICON 图标，替换 public/favicon.ico
            if (preg_match('#^https?://#', $post['site_icon'] ?? '')) try {
                SystemService::setFavicon($post['site_icon'] ?? '');
            } catch (\Exception $exception) {
                trace_file($exception);
            }
            // 数据数据到系统配置表
            foreach ($post as $k => $v) sysconf($k, $v);
            sysoplog('系统配置管理', "修改系统参数成功");
            $this->success('数据保存成功！', admuri('admin/config/index'));
        }
    }

    /**
     * 修改文件存储
     * @auth true
     * @throws \think\admin\Exception
     */
    public function storage()
    {
        $this->_applyFormToken();
        if ($this->request->isGet()) {
            $this->type = input('type', 'local');
            if ($this->type === 'alioss') {
                $this->points = AliossStorage::region();
            } elseif ($this->type === 'qiniu') {
                $this->points = QiniuStorage::region();
            } elseif ($this->type === 'txcos') {
                $this->points = TxcosStorage::region();
            }
            $this->fetch("storage-{$this->type}");
        } else {
            $post = $this->request->post();
            if (!empty($post['storage']['allow_exts'])) {
                $deny = ['sh', 'asp', 'bat', 'cmd', 'exe', 'php'];
                $exts = array_unique(str2arr(strtolower($post['storage']['allow_exts'])));
                if (count(array_intersect($deny, $exts)) > 0) $this->error('禁止上传可执行的文件！');
                $post['storage']['allow_exts'] = join(',', $exts);
            }
            foreach ($post as $name => $value) sysconf($name, $value);
            sysoplog('系统配置管理', "修改系统存储参数");
            $this->success('修改文件存储成功！');
        }
    }
}