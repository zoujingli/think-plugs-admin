# ThinkPlugsAdmin

基于`ThinkAdmin`的`admin`模块，后台的基础核心组件。

目前代码主仓库放在`Gitee`,`Github`仅为镜像仓库用于发布`Composer`包。

安装时会占用并替换`app/admin`目录 ( 强制先删再写 )，因此需要注意是否有对`app/admin`的文件进行修改，若有修改时我们不建议安装此模块，否则会造成修改的内容丢失！

#### 全新或更新安装

```shell
composer require zoujingli/think-plugs-admin
```

#### 启动测试环境并访问 http://127.0.0.1:8000

```shell
php think run --host 127.0.0.1
```