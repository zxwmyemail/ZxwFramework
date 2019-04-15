<?php
if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');
/********************************************************************************************
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 ********************************************************************************************/

class homeController extends Controller {
        
    public function __construct() {
        parent::__construct();
    }

    public function page404Action() {
        $this->smarty->display('404Page.html'); 
        exit();
    }

    public function testAction() {

        //测试smarty模板
        $this->smarty->display('home.html'); 
        exit();
        
        //测试重定向
        $this->redirect('page404'); 
        exit();

        //测试日志记录函数
        $logIns = Log::getInstance();
        $logIns->logMessage("test",Log::INFO,'myTest'); 
        die();

        //测试手动加载类函数
        $model = Application::newObject('MyTest','public');
        var_dump($model);
        die();


        //测试静态类
        $result = Util::get_rand(array('0'=>30,'1'=>40,'2'=>20,'3'=>10));
        var_dump($result);die();


        //测试自定义类加载，可自动加载mvc/model/、system/library/、system/core/这些文件夹下面的类
        $homeModel = new homeModel();
        $homeModel -> testResult();
        exit();
    }
    
    public function indexAction() {
        error_reporting(0);
        $os = explode(" ", php_uname());
        $serverParam = array(
            'server_domain'          => $_SERVER['SERVER_NAME']. "(" .('/'==DIRECTORY_SEPARATOR ? $_SERVER['SERVER_ADDR']: @gethostbyname($_SERVER['SERVER_NAME'])) .")",
            'server_operate_system'  => $os[0] . '&nbsp;内核版本：'.('/'==DIRECTORY_SEPARATOR ? $os[2] : $os[1]),
            'server_engine'          => $_SERVER['SERVER_SOFTWARE'],
            'server_hostname'        => '/'== DIRECTORY_SEPARATOR ? $os[1] : $os[2],
            'server_flag'            => $sysInfo['win_n'] != '' ? $sysInfo['win_n'] : @php_uname(),
            'server_port'            => $_SERVER['SERVER_PORT'],
        );

        $phpParam = array(
            'php_version'                => PHP_VERSION,
            'php_sapi_name'              => strtoupper(php_sapi_name()),
            'php_memory_limit'           => $this->valueIsOk("memory_limit"),
            'php_safe_mode'              => $this->valueIsOk("safe_mode"),
            'php_post_max_size'          => $this->valueIsOk("post_max_size"),
            'php_upload_max_filesize'    => $this->valueIsOk("upload_max_filesize"),
            'php_precision'              => $this->valueIsOk("precision"),
            'php_max_execution_time'     => $this->valueIsOk("max_execution_time"),
            'php_default_socket_timeout' => $this->valueIsOk("default_socket_timeout"),
            'php_doc_root'               => $this->valueIsOk("doc_root"),
            'php_user_dir'               => $this->valueIsOk("user_dir"),
            'php_enable_dl'              => $this->valueIsOk("enable_dl"),
            'php_include_path'           => $this->valueIsOk("include_path"),
            'php_display_errors'         => $this->valueIsOk("display_errors"),
            'php_register_globals'       => $this->valueIsOk("register_globals"),
            'php_magic_quotes_gpc'       => $this->valueIsOk("magic_quotes_gpc"),
            'php_short_open_tag'         => $this->valueIsOk("short_open_tag"),
            'php_asp_tags'               => $this->valueIsOk("asp_tags"),
            'php_ignore_repeated_errors' => $this->valueIsOk("ignore_repeated_errors"),
            'php_ignore_repeated_source' => $this->valueIsOk("ignore_repeated_source"),
            'php_report_memleaks'        => $this->valueIsOk("report_memleaks"),
            'php_magic_quotes_gpc'       => $this->valueIsOk("magic_quotes_gpc"),
            'php_magic_quotes_runtime'   => $this->valueIsOk("magic_quotes_runtime"),
            'php_allow_url_fopen'        => $this->valueIsOk("allow_url_fopen"),
            'php_register_argc_argv'     => $this->valueIsOk("register_argc_argv"),
            'php_cookie'                 => isset($_COOKIE)?'<font color="green">√</font>' : '<font color="red">×</font>',
            'php_aspell_check_raw'       => $this->isfun("aspell_check_raw"),
            'php_bcadd'                  => $this->isfun("bcadd"),
            'php_preg_match'             => $this->isfun("preg_match"),
            'php_pdf_close'              => $this->isfun("pdf_close"),
            'php_snmpget'                => $this->isfun("snmpget"),
            'php_vm_adduser'             => $this->isfun("vm_adduser"),
            'php_curl_init'              => $this->isfun("curl_init"),
            'php_smtp'                   => get_cfg_var("SMTP")?'<font color="green">√</font>' : '<font color="red">×</font>',
            'php_smtp_addr'              => get_cfg_var("SMTP")?get_cfg_var("SMTP"):'<font color="red">×</font>',
            'php_disable_functions'      => $this->getDisableFun(),
        );
        
        $zend_version = zend_version();
        $plugin_ioncube = '';
        if(extension_loaded('ionCube Loader')){   
            $ys = ioncube_loader_iversion();   
            $gm = ".".(int)substr($ys,3,2);   
            $plugin_ioncube = ionCube_Loader_version().$gm;
        } else {
            $plugin_ioncube = "<font color=red>×</font>";
        }
        $plugin_gd = '';
        if(function_exists(gd_info)) {
            $gd_info = @gd_info();
            $plugin_gd = $gd_info["GD Version"];
        } else {
            $plugin_gd = '<font color="red">×</font>';
        }
        $pluginParam = array(
            'plugin_zend_version'        => empty($zend_version) ? '<font color=red>×</font>' : $zend_version,
            'plugin_ldap_close'          => $this->isfun("ldap_close"),
            'plugin_eAccelerator'        => (phpversion('eAccelerator'))!='' ? phpversion('eAccelerator') : "<font color=red>×</font>",
            'plugin_ioncube'             => $plugin_ioncube,
            'plugin_XCache'              => (phpversion('XCache'))!='' ? phpversion('XCache') : "<font color=red>×</font>",
            'plugin_APC'                 => (phpversion('APC'))!='' ? phpversion('APC') : "<font color=red>×</font>",
            'plugin_ftp_login'           => $this->isfun("ftp_login"),
            'plugin_xml_set_object'      => $this->isfun("xml_set_object"),
            'plugin_session_start'       => $this->isfun("session_start"),
            'plugin_socket_accept'       => $this->isfun("socket_accept"),
            'plugin_cal_days_in_month'   => $this->isfun("cal_days_in_month"),
            'plugin_allow_url_fopen'     => $this->valueIsOk("allow_url_fopen"),
            'plugin_gd'                  => $plugin_gd,
            'plugin_gzclose'             => $this->isfun("gzclose"),
            'plugin_imap_close'          => $this->isfun("imap_close"),
            'plugin_JDToGregorian'       => $this->isfun("JDToGregorian"),
            'plugin_preg_match'          => $this->isfun("preg_match"),
            'plugin_wddx_add_vars'       => $this->isfun("wddx_add_vars"),
            'plugin_iconv'               => $this->isfun("iconv"),
            'plugin_mb_eregi'            => $this->isfun("mb_eregi"),
            'plugin_mcrypt_cbc'          => $this->isfun("mcrypt_cbc"),
            'plugin_mhash_count'         => $this->isfun("mhash_count"),
        );

        $this->smarty->assign('serverParam',$serverParam);
        $this->smarty->assign('phpParam',$phpParam);
        $this->smarty->assign('pluginParam',$pluginParam);
        $this->smarty->display('probe.html'); 
    }

    public function getPhpInfoAction() {
        phpinfo();exit();
    }

    public function getEnableFunAction() {
        $arr = get_defined_functions();
        Function php(){}
        echo "<pre>","这里显示系统所支持的所有函数,和自定义函数\n";
        print_r($arr);echo "</pre>";exit();
    }


    // *********************************   以下为本类内部调用函数  *******************************************
    private function valueIsOk($varName){
        switch($result = get_cfg_var($varName))
        {
            case 0:
                return '<font color="red">×</font>';
                break;
            case 1:
                return '<font color="green">√</font>';
                break;
            default:
                return $result;
                break;
        }
    }

    private function isfun($funName = '')
    {
        if (!$funName || trim($funName) == '' || preg_match('~[^a-z0-9\_]+~i', $funName, $tmp)) return '错误';
        return (false !== function_exists($funName)) ? '<font color="green">√</font>' : '<font color="red">×</font>';
    } 

    private function getDisableFun()
    {
        $result = '';
        $disFuns=get_cfg_var("disable_functions");
        if(empty($disFuns)) {
            $result = '<font color=red>无</font>';
        } else { 
            $disFuns_array =  explode(',',$disFuns);
            foreach ($disFuns_array as $key => $value) 
            {
                if ($key!=0 && $key%5==0) {
                    $result .= '<br />';
                }
                $result .= "$value&nbsp;&nbsp;";
            }   
        }

        return $result;
    } 
}

