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
（2）制作表单，以 **input[type=text]** 举例说明如下：

```html
<form id="demo">
    <input type="text" name="name" 
           required="true" 
           validate-type="chinese" 
           min='2' max='4' 
           tipmsg="必填项" 
           errmsg="只允许2-4个中文"
    />
    <span style="color:red" id="name-tip"></span>
  
    <input type="button" id="saveBtn"/>
</form>
  
1. required：是否必填；validate-type校验类型；min字符串最小长度；max字符串最大长度；tipmsg为input中没有文本时的提示语；
   errmsg为用户输入错误时的提示语；
2. 上面的提示语显示位置需要开发人员自己单独指定，比如上面的id值为name-tip的span标签就是显示提示语的，该标签需指定id属性
   值，属性值的规则为input的name属性值拼接上"-tip"，比如上例中为 name-tip.
```
<br>
（3）对表单验证添加js的初始化校验代码，如下：

```js
$('#demo').checkForm({
    submitBtnId : 'saveBtn',       // 提交按钮的id值
    onSubmitHandle : function(){   // 校验完毕后的提交事件，可发送ajax请求之类的
        alert('校验通过');          // #code 这里做验证通过后的操作，如提交表单等
    }
});
```







