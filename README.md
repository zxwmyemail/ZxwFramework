##ZxwFramework - 基于MVC的php框架

> - 作者： iProg
> - 日期： 2015-01-12
> - 版本： 1.1.1
> - 邮箱： zxwmyemail@126.com
> - 描述： It is a PHP framework based on the MVC design pattern!

<br>
####相关说明
<br>
一、系统目录和主要文件如下：
```html
    |-bootstrap                 web入口文件和静态资源所在文件夹
        |--index.php            web入口文件
        |--resource             静态资源所在文件夹
            |---css             css文件存放位置 
            |---js              js文件存放位置
            |---images          images文件存放位置
            |---font            font文件存放位置
    |-config                    存放配置文件  
        |--const.config.php     系统预定义常量 
        |--params.config.php    系统参数配置文件
    |-log                       日志文件夹
        |--sys_log              系统日志
        |--app_log              程序日志
    |-mvc                       框架的mvc层
        |--controller           控制器文件  
        |--model                模型文件  
        |--view                 视图文件     
    |-public                    存放自定义公共类库  
    |-system                    系统目录 
        |--core                 系统核心类，例如控制层父类，model层父类、路由类
        |--framework            第三方框架，例如smarty引擎
        |--library              系统类库
    |-readme.txt                框架说明文件，即本文件
```
<br>
二、系统文件名命名规则：
```html
    所有自定义类，文件名和类名必须一样，如：类名为MyTest，则文件名也应为MyTest
```
<br>
三、系统集成了smarty引擎，在控制层中使用方法如下：
```html
    $this->smarty->assign('name','zxw');
    $this->smarty->display('home.html');
```







