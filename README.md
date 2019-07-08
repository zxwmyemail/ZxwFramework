ZxwFramework - 基于MVC的php框架，支持composer，懒加载
---

> - 作者： iProg
> - 日期： 2019-07-8
> - 版本： 2.0.1
> - 邮箱： zxwmyemail@126.com
> - 描述： It is a PHP framework based on the MVC design pattern!


框架相关说明 - 轻量级、易使用
---

一、系统目录和主要文件如下：
```
  | — app                        应用程序所在目录
       | —— home                     home模块目录，框架支持模块，app目录下建立一个文件夹，即为一个目录
            | ——— controllers            控制层
            | ——— models                 model层
            | ——— views                  视图层
       | —— runtime                  日志文件夹
            | ——— sys_log                系统日志
            | ——— app_log                程序日志
  | — web                        web入口文件和静态资源所在文件夹
       | —— index.php                web入口文件
       | —— alibaba                  阿里巴巴支付通知文件所在文件夹
       | —— qrcode                   二维码生成图片默认存储文件夹
       | —— wechat                   微信支付通知文件所在文件夹
       | —— asset                    静态资源所在文件夹
            | ——— css                    css文件存放位置 
            | ——— js                     js文件存放位置
            | ——— images                 images文件存放位置
            | ——— font                   font文件存放位置
  | — config                     存放配置文件  
      | —— const.php                 系统预定义常量 
      | —— config.php                系统参数配置文件
      | —— alibaba.php               阿里巴巴支付配置文件
      | —— wechat.php                微信支付配置文件
      | —— database.php              mysql数据库连接参数配置文件
      | —— email.php                 邮件配置文件
      | —— redis.php                 redis配置文件 
  | — core                       框架核心类目录 
      | —— extend                    扩展类
      | —— system                    系统核心类
      | —— library                   框架类库
      | —— vendor                    composer类库
  | — README.md                  框架说明文件，即本文件
```

二、系统文件名命名规则：
```
  所有自定义类，文件名和类名必须一样，如：类名为MyTest，则文件名也应为MyTest
```

三、系统集成了smarty引擎，在控制层中使用方法如下：
```
  $this->smarty->assign('name','zxw');
  $this->smarty->display('home.html');
```
四、系统多模块使用说明：
```
  1.该框架支持多个模块，比如有网站前台（module名为home）和网站后台管理（module名为backend）
    两个系统（一个系统对应一个模块），所以要有两个控制层，先在app/文件夹下面建立两个文件夹，
    规则如下：
    网站前台：app/home/
    网站后台：app/backend/

    每个模块下面都有controllers、models、views三个文件夹

  2.无论哪个模块，如果在控制层建立了一个控制层类，对应的，在视图层要先建立文件夹，比如：
    在网站前台的控制层：app/home/controllers/下建立了一个控制层类文件Home.php
    则需在对应的视图层：app/home/views/下先建立home文件夹(与控制层类名一样，首字母小写)，html页面就放在home下面

  3.路由访问，有两种:
    (1) http://localhost/web/index.php?m=home&r=home.index&id=2
    (2) http://localhost/web/index.php/home/controller/action/?id=2

    说明，默认的系统模块为home（见config/config.php中关于默认路由配置），
    所以可以不写，即为：
    (1) http://localhost/web/index.php?r=home.index&id=2
    (2) http://localhost/web/index.php/controller/action/?id=2  
```

五、类加载机制：
```
  使用composer类自动加载机制，其中框架预先有两个全局命名空间：
  1） app： 对应app文件夹；
  2） core： 对应core文件夹；
    
  推荐在app下面扩展自己的类，举例：
  比如在app文件夹下建立一个文件夹为public，然后，在public下构建自己的类，类的命名空间就是：app\public;
  命名空间需要与类文件所在的路径一致，比如类在app/public/Test.php，则Test.php的命名空间就是 app\public；
```



