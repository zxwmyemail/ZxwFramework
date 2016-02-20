<?php

if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');

/********************************************************************************************
 * 系统配置文件
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 ********************************************************************************************/

/*-------------------------------------------------------------------------------------------
| mysql数据库参数配置
|--------------------------------------------------------------------------------------------
| 模式说明
| 开发模式为development、测试模式为test、生产环境模式为product
|
| 参数说明
| db_conn   数据库连接表示，1为长久链接，0为即时链接
-------------------------------------------------------------------------------------------*/
$_CONFIG['system']['mysql'] = array(
    'development' => array(
        'default' => array(
            'db_host'          => 'localhost',
            'db_user'          => 'root',
            'db_port'          => '3306',
            'db_password'      => '',
            'db_database'      => 'mysql',
            'db_table_prefix'  => 'app_',
            'db_charset'       => 'utf8',
            'db_conn'          => 0
        ),
        'other'  => array(
            'db_host'          => 'localhost',
            'db_user'          => 'root',
            'db_port'          => '3306',
            'db_password'      => '',
            'db_database'      => 'mysql',
            'db_table_prefix'  => 'app_',
            'db_charset'       => 'utf8',
            'db_conn'          => 0
        )
    ),
    'test' => array(
        'default' => array(
            'db_host'          => 'localhost',
            'db_user'          => 'root',
            'db_port'          => '3306',
            'db_password'      => '',
            'db_database'      => 'mysql',
            'db_table_prefix'  => 'app_',
            'db_charset'       => 'utf8',
            'db_conn'          => 0
        ),
        'other'  => array(
            'db_host'          => 'localhost',
            'db_user'          => 'root',
            'db_port'          => '3306',
            'db_password'      => '',
            'db_database'      => 'mysql',
            'db_table_prefix'  => 'app_',
            'db_charset'       => 'utf8',
            'db_conn'          => 0
        )
    ),
    'product' => array(
        'default' => array(
            'db_host'          => 'localhost',
            'db_user'          => 'root',
            'db_port'          => '3306',
            'db_password'      => '',
            'db_database'      => 'mysql',
            'db_table_prefix'  => 'app_',
            'db_charset'       => 'utf8',
            'db_conn'          => 0
        ),
        'other'  => array(
            'db_host'          => 'localhost',
            'db_user'          => 'root',
            'db_port'          => '3306',
            'db_password'      => '',
            'db_database'      => 'mysql',
            'db_table_prefix'  => 'app_',
            'db_charset'       => 'utf8',
            'db_conn'          => 0
        )
    )
);


/*------------------------------------------------------------------------------------------
| oracle数据库参数配置
|-------------------------------------------------------------------------------------------
| 模式说明
| 开发模式为development、测试模式为test、生产环境模式为product
|
| 参数说明
| 'db_host'             数据库主机地址
| 'db_port'             数据库端口
| 'db_server_name'      数据库服务名称
| 'db_user'             数据库账户
| 'db_password'         数据库密码 
-------------------------------------------------------------------------------------------*/
$_CONFIG['system']['oracle'] = array(
    'development' => array(
        'default' => array(
            'db_host'          => 'localhost', 
            'db_port'          => '1521',
            'db_server_name'   => '',
            'db_user'          => '',
            'db_password'      => '',
            'db_charset'       => 'utf8',
            'db_conn'          => 0
        ),
        'other'   => array(
            'db_host'          => 'localhost', 
            'db_port'          => '1521',
            'db_server_name'   => '',
            'db_user'          => '',
            'db_password'      => '',
            'db_charset'       => 'utf8',
            'db_conn'          => 0
        )
    ),
    'test' => array(
        'default' => array(
            'db_host'          => 'localhost', 
            'db_port'          => '1521',
            'db_server_name'   => '',
            'db_user'          => '',
            'db_password'      => '',
            'db_charset'       => 'utf8',
            'db_conn'          => 0
        ),
        'other'   => array(
            'db_host'          => 'localhost', 
            'db_port'          => '1521',
            'db_server_name'   => '',
            'db_user'          => '',
            'db_password'      => '',
            'db_charset'       => 'utf8',
            'db_conn'          => 0
        )
    ),
    'product' => array(
        'default' => array(
            'db_host'          => 'localhost', 
            'db_port'          => '1521',
            'db_server_name'   => '',
            'db_user'          => '',
            'db_password'      => '',
            'db_charset'       => 'utf8',
            'db_conn'          => 0
        ),
        'other'   => array(
            'db_host'          => 'localhost', 
            'db_port'          => '1521',
            'db_server_name'   => '',
            'db_user'          => '',
            'db_password'      => '',
            'db_charset'       => 'utf8',
            'db_conn'          => 0
        )
    )
);


/*-------------------------------------------------------------------------------------------
| 默认路由配置和url形式配置
|--------------------------------------------------------------------------------------------
| 使用说明 
| 1.default_controller  系统默认控制器
| 2.default_action      系统默认控制器方法
| 3.url_type            定义URL的形式
|                       1为普通模式   index.php?c=controller&a=action&id=2
|                       2为PATHINFO   index.php/controller/action/?id=2
--------------------------------------------------------------------------------------------*/
$_CONFIG['system']['route'] = array(
    'default_module'     => 'home',
    'default_controller' => 'home', 
    'default_action'     => 'index', 
    'url_type'           => 1                                                                           
);


/*------------------------------------------------------------------------------------------
| redis缓存配置
|-------------------------------------------------------------------------------------------
| 参数说明 
| 1.host             主机IP
| 2.auth             主机授权密码
| 3.port             端口号
------------------------------------------------------------------------------------------*/
$_CONFIG['system']['redis'] = array(
    'development' => array(
        'master' => array(
            'host'=>'127.0.0.1',
            'auth'=>'123456',
            'port'=>'6379'
        ),
        'slave1' => array(
            'host'=>'127.0.0.1',
            'auth'=>'123456',
            'port'=>'6379'
        )
    ),
    'test' => array(
        'master' => array(
            'host'=>'127.0.0.1',
            'auth'=>'123456',
            'port'=>'6379'
        ),
        'slave1' => array(
            'host'=>'127.0.0.1',
            'auth'=>'123456',
            'port'=>'6379'
        )
    ),
    'product' => array(
        'master' => array(
            'host'=>'127.0.0.1',
            'auth'=>'123456',
            'port'=>'6379'
        ),
        'slave1' => array(
            'host'=>'127.0.0.1',
            'auth'=>'123456',
            'port'=>'6379'
        )
    ),
);


/*------------------------------------------------------------------------------------------
| memcache缓存配置
|-------------------------------------------------------------------------------------------
| 参数说明 
| 1.server             memcache服务器地址端口配置，weight为优先级
| 2.expiration         过期时间
| 3.prefix             键值前缀
| 4.compression        使用MEMCACHE_COMPRESSED标记对数据进行压缩(使用zlib)。
| 5.isAutoTresh        是否开启大值自动压缩
| 6.threshold          控制多大值进行自动压缩的阈值
| 7.thresavings        指定经过压缩实际存储的值的压缩率，支持的值必须在0和1之间。
|                      默认值是0.2表示20%压缩率。
-------------------------------------------------------------------------------------------*/
$_CONFIG['system']['memcache'] = array(
    'development' => array(
        'server' => array(
            array('host' => '127.0.0.1', 'port' => '11211', 'weight' => 1),
            array('host' => '127.0.0.1', 'port' => '11211', 'weight' => 1)
        ),
        'expiration'    =>  18600, 
        'prefix'        =>  'zxw_', 
        'compression'   =>  false,
        'isAutoTresh'   =>  true,
        'threshold'     =>  20000, 
        'thresavings'   =>  0.2 
    ),
    'test' => array(
        'server' => array(
            array('host' => '127.0.0.1', 'port' => '11211', 'weight' => 1),
            array('host' => '127.0.0.1', 'port' => '11211', 'weight' => 1)
        ),
        'expiration'    =>  18600, 
        'prefix'        =>  'zxw_', 
        'compression'   =>  false,
        'isAutoTresh'   =>  true,
        'threshold'     =>  20000, 
        'thresavings'   =>  0.2 
    ),
    'product' => array(
        'server' => array(
            array('host' => '127.0.0.1', 'port' => '11211', 'weight' => 1),
            array('host' => '127.0.0.1', 'port' => '11211', 'weight' => 1)
        ),
        'expiration'    =>  18600, 
        'prefix'        =>  'zxw_', 
        'compression'   =>  false,
        'isAutoTresh'   =>  true,
        'threshold'     =>  20000, 
        'thresavings'   =>  0.2 
    ),
);


/*------------------------------------------------------------------------------------------
| 注册类加载文件夹路径
|-------------------------------------------------------------------------------------------
| 使用说明 
| 1.先注册文件夹路径，如下所示
| 2.然后创建对象使用：$mytest = Application::newClass('MyTest','public');
-------------------------------------------------------------------------------------------*/
$_CONFIG['system']['newClassPath'] = array(
    'public'    => APP_PUBLIC_PATH,
    'smarty'    => SYS_FRAMEWORK_PATH.'/smarty/libs',
    'jsonRPC'   => SYS_FRAMEWORK_PATH.'/rpc/jsonRPC'
);

