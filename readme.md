# ThinkPlugsAdmin for ThinkAdmin

[![Latest Stable Version](https://poser.pugx.org/zoujingli/think-plugs-admin/v/stable)](https://packagist.org/packages/zoujingli/think-plugs-admin)
[![Latest Unstable Version](https://poser.pugx.org/zoujingli/think-plugs-admin/v/unstable)](https://packagist.org/packages/zoujingli/think-plugs-admin)
[![Total Downloads](https://poser.pugx.org/zoujingli/think-plugs-admin/downloads)](https://packagist.org/packages/zoujingli/think-plugs-admin)
[![Monthly Downloads](https://poser.pugx.org/zoujingli/think-plugs-admin/d/monthly)](https://packagist.org/packages/zoujingli/think-plugs-admin)
[![Daily Downloads](https://poser.pugx.org/zoujingli/think-plugs-admin/d/daily)](https://packagist.org/packages/zoujingli/think-plugs-admin)
[![PHP Version](https://doc.thinkadmin.top/static/icon/php-7.1.svg)](https://thinkadmin.top)
[![License](https://doc.thinkadmin.top/static/icon/license-mit.svg)](https://mit-license.org)

**ThinkAdmin** 核心插件，后台基础管理模块，开源免费可商用！

代码主仓库放在 **Gitee**，**Github** 仅为镜像仓库用于发布 **Composer** 包。

安装此插件会占用并替换 `app/admin` 目录 ( 先删再写 )，若有对 `app/admin` 有修改不建议安装此插件，否则会造成修改的内容丢失！

使用 `Composer` 卸载此插件时，不会删除 `app/admin` 目录和对应数据表，需要手动删除目录和数据表。

如果不希望自有的 `app/admin` 目录被更新替换，可以在 `app/admin` 目录下创建 `ignore` 文件（ 如 `app/admin/ignore`，注意文件名没有后缀哦！），即使执行了插件安装或更新都会忽略更新替换。

### 插件文档

https://thinkadmin.top/plugin/think-plugs-admin.html

### 安装插件

```shell
### 安装前建议尝试更新所有组件
composer update --optimize-autoloader

### 注意，插件仅支持在 ThinkAdmin v6.1 中使用
composer require zoujingli/think-plugs-admin --optimize-autoloader
```

### 卸载插件

```shell
### 插件卸载不会删除数据表和 app/admin 的代码
### 卸载后通过 composer update 时不会再更新，其他依赖除外
composer remove zoujingli/think-plugs-admin
```

### 功能节点

可根据下面的功能节点配置菜单和访问权限，按钮操作级别的节点未展示！

* 系统参数配置：`admin/config/index`
* 系统任务管理：`admin/queue/index`
* 系统日志管理：`admin/oplog/index`
* 数据字典管理：`admin/base/index`
* 系统文件管理：`admin/file/index`
* 系统菜单管理：`admin/menu/index`
* 访问权限管理：`admin/auth/index`
* 系统用户管理：`admin/user/index`

### 插件数据库

本插件涉及数据表有：

* 系统-权限：`system_auth`
* 系统-授权：`system_auth_node`
* 系统-字典：`system_base`
* 系统-配置：`system_config`
* 系统-数据：`system_data`
* 系统-文件：`system_file`
* 系统-菜单：`system_menu`
* 系统-日志：`system_oplog`
* 系统-任务：`system_queue`
* 系统-用户：`system_user`

### 版权说明

**ThinkPlugsAdmin** 遵循 **MIT** 开源协议发布，并免费提供使用。

本项目包含的第三方源码和二进制文件的版权信息将另行标注，请在对应文件查看。

版权所有 Copyright © 2014-2023 by ThinkAdmin (https://thinkadmin.top) All rights reserved。
