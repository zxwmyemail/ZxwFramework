<?php
namespace app\home\controllers;

use app\home\models\Articles;
use app\home\models\Probe;
use core\extend\phpoffice\PhpExcel;
use core\extend\captcha\Captcha;
use core\extend\monolog\Log;
use core\extend\overtrue\CntoPy;
use core\extend\phpemail\PhpEmail;
use core\extend\validate\Validate;
use core\extend\qrcode\MyQrCode;

class Home extends Base
{	
  	/**
     * 探针页面
     */
  	public function index() {
    	$probe = new Probe();
        $serverParam = $probe->getServerParam();
        $phpParam    = $probe->getPhpParam();
        $pluginParam = $probe->getPluginParam();

        $this->smarty->assign('serverParam', $serverParam);
        $this->smarty->assign('phpParam', $phpParam);
        $this->smarty->assign('pluginParam', $pluginParam);
        $this->smarty->display('probe.html'); 
  	}

  	public function phpInfo() {
        phpinfo();
    }

    /**
     * 500页面
     */
  	public function page500() {
        $this->smarty->display('page500.html'); 
    }

    /**
     * 测试汉字转拼音
     */
    public function testCn() {
        var_dump(CntoPy::sentence('您好，我在测试汉字转拼音！'));
    }

    /**
     * 测试参数校验
     */
    public function testCheck() {
        //验证规则
        $validations = [
            "offset" => "IntGe:0", // 参数offset应该大于等于0
            "count" => "Required|IntGeLe:1,200", // 参数count是必需的且大于等于1小于等于200
            "type" => "IntIn:1,2", // 参数type可取值为: 1, 2
            "state" => [
                'IfIntEq:type,1|IntEq:0', // 如果type==1，那么参数state只能是0
                'IfIntEq:type,2|IntIn:0,1,2', // 如果type==2，那么参数state可取值为: 1, 2, 3
            ],
            "phone" => "Regexp:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199|(147))\\d{8}$/", // 验证手机号
            "email" => "Email", // 验证邮箱
        ];

        // 待验证$_REQUEST参数
        $params = [
            "offset" => 0,  // 从第0条记录开始
            "count"  => 10, // 最多返回10条记录
            "type"   => 2,  // 1-批评建议, 2-用户投诉
            "state"  => 0,  // 0-待处理, 1-处理中, 2-已处理
            "phone"  => '13996633555', 
            "email"  => 'zxw@163.com', 
        ];

        $result = Validate::check($params, $validations);
        if ($result === true) {
            echo '验证通过';
        } else {
            echo $result;
        }

    }

    /**
     * 测试发邮件
     */
    public function testEmail() {
        $email = PhpEmail::getInstance();
        $email->send('你好，请教一个问题', 'testBodydkfejlkrjekkrelkrelrker', ['1440552721@qq.com']);
    }

    /**
     * 测试日志记录
     */
    public function testLog() {
        $log = Log::getInstance(); 
        $log->error('测试错误日志');
    }

    /**
     * 测试二维码
     */
    public function testQrcode() {
        MyQrCode::get('https://www.baidu.com'); 
    }

    /**
     * 测试redis
     */
    public function testRedis() {
        $redis = $this->getCache('redis'); 
        var_dump($redis);die();
    }

    /**
     * 测试Illuminate\Database组件
     */
    public function testModel() {
        $article = Articles::getFirst();
    	var_dump($article);die();
    }

    /**
     * 测试导入excel为数组
     */
    public function testImportExcel() {
        $ret = PhpExcel::importExecl(BASE_PATH . '/test.xlsx');
    	  var_dump($ret);die();
    }

    /**
     * 测试导出到excel
     */
    public function testExportExcel() {
      	$fields = ['ID','姓名','性别'];
      	$datas = [
            ['id' => '1', 'name' => '李逵', 'sex' => '男'], 
            ['id' => '2', 'name' => '松江', 'sex' => '男']
        ];
      	PhpExcel::exportExcel('test', $datas, $fields);
    }

    /**
     * 测试验证码
     */
    public function testVerifyCode() {
      	$captcha = new Captcha(5);
  		  $captcha->output();
    }
}