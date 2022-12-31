# ThinkPlugsAdmin

基于`ThinkAdmin`的`admin`模块。

安装时会替换`app/admin`目录，因此要注意是否有对`app/admin`进行修改，如果有修改我们不建议安装此模块！

由于`layui 2.8`未正式发布，所以这里只用了`rc`版本，待其正式发布之后我们也会相应发布上线。

#### 全新或更新安装 ( 空目录或项目根目录下执行 )

```shell
composer require zoujingli/think-plugs-admin
```

#### 启动测试环境并访问 http://127.0.0.1:8000

```shell
php think run --host 127.0.0.1
```