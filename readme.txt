/*************************************************************************
 * 本文件为框架的说明文件
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 ************************************************************************/

一、系统目录和主要文件如下：  
    |-mvc                       框架的mvc层
        |--controller               控制器文件  
        |--model                    模型文件  
        |--view                     视图文件     
    |-public                    存放自定义公共类库  
    |-log                       系统报错日志
    |-resource                  系统资源文件
        |--css                      css文件存放位置 
        |--js                       js文件存放位置
        |--images                   images文件存放位置
        |--font                     font文件存放位置
    |-config                    存放配置文件  
        |--const.config.php         系统预定义常量 
        |--params.config.php        系统参数配置文件
    |-system                    系统目录 
        |--core                     系统核心类，例如控制层父类，model层父类、路由类
        |--framework                第三方框架，例如smarty引擎
        |--library                  系统类库
    |-index.php                 入口文件
    |-readme.txt                框架说明文件，即本文件


二、系统文件名命名规则
    所有自定义类，文件名和类名必须一样，如：类名为MyTest，则文件名也应为MyTest 


三、系统集成了smarty引擎，在控制层中使用方法如下：
    $this->smarty->assign('name','zxw');
    $this->smarty->display('home.html');


四、文件夹mvc/model/、mvc/view/下面，建立文件夹的规则和控制层类的对应关系举例如下：
    如果    mvc/controller下面建立了一个控制层类homeController
    那么    mvc/model/下面应该新建一个home文件夹
           mvc/view/下面也应该新建一个home文件夹  


五、类加载机制：
    1.自动加载，这种加载，只对下面文件夹下的类有用
      mvc/model 、system/library 和  system/core
      如果类在这些文件夹下面，只需正常操作即可，比如 $model = new model();

    2.手动加载，这种需做配置，主要用于对自己写的类进行加载，步骤：
      a、先建一个public文件夹，比如将文件夹建在与mvc文件夹同级的位置（原则上可以任意位置）
      b、然后在config/params.config.php里面配置public的路径，配置如下：
            $CONFIG['system']['newClassPath'] = array(
                'public' => '/ZxwFramework/public'
            );
      c、如果想创建public下面的对象的时候，可做如下操作：
            第一个参数为类名，第二个为上面配置的键名public：
            $model = Application::newClass('MyTest','public');

      注意：手动加载的情况下，public下面可以任意建立多级文件夹，但是public下面的文件名不能重名！


