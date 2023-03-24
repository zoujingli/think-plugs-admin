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

use think\migration\Migrator;

@set_time_limit(0);
@ini_set('memory_limit', -1);

/**
 * 系统模块数据
 */
class InstallAdmin20230325 extends Migrator
{
    public function change()
    {
        $this->_upgrade_system_file();
    }

    /**
     * 创建数据对象
     * @class SystemFile
     * @table system_file
     * @return void
     */
    private function _upgrade_system_file()
    {

        // 当前数据表
        $table = 'system_file';
        // 检查与更新数据表
        if (!$this->table($table)->hasColumn('unid')) $this->table($table)
            ->addColumn('unid', 'integer', ['limit' => 20, 'default' => 0, 'null' => true, 'after' => 'unid', 'comment' => '会员编号'])
            ->addIndex('unid', ['name' => 'idx_system_file_unid'])
            ->save();
    }
}
