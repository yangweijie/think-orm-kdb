# think-orm-kda
人大金仓 驱动 for think-orm

## 安装

### 目前win只有 7.2 和 5.6 两个版本
`v9r1_pdo_kdb_win.zip`

找到nts （对应本地php thread safe）的复制到ext里 改 `php.ini`

~~~ ini
extension=php_pdo_kdb.dll
~~~

php -m 后 如果 提示以下错误：

~~~
PHP Warning:  PHP Startup: Unable to load dynamic library
~~~

则需要把数据库安装目录下的D:\soft\Kingbase\ES\V9\Server\bin中的dll文件和D:\soft\Kingbase\ES\V8\jre\bin中的dll文件都拷贝至C:\Windows\System32目录下。

## 框架配置

`config/database.php`中配置

~~~ php
'default'='kdb',
'kdb'=>[
    'type'=>'kdb',
    'hostname'=>'localhost',
    'hostport'=>54321,
    'username'=>'sysdba',
    'password' => 'sysdba',
    'database'=>'blog',
    'charset' => Env::get('database.charset', 'utf8'),
    'prefix' => 'dp_',
]
~~~

## 兼容性

### `find_in_set`

参见src/connector/kdb.sql

### json
json 函数必须将字段设置为json 或 jsonb

json_set 函数没有 所以 

~~~ php
Test::json(['content'])->where('id', 1)->update([
    'content->nickname'=>'流年2'
]);
~~~ 

这种写法更定json字段里单路径 不支持

### 主键自增

[添加序列 字段设默认值来实现](https://blog.csdn.net/lty13142/article/details/121634249)

## 数据库管理和迁移

### navicat

直接以pgsql 方式可以连接


## 难点

- 报错 乱码问题



