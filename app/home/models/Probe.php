<?php
namespace app\home\models;

use core\system\Config;

class Probe {

    private $ok = '<font style="color:green;">√</font>';
    private $no = '<font style="color:red;">×</font>';

    public function getServerParam() {
        $os = explode(" ", php_uname());
        $isWin = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? true : false; 
        $ipAddress = $isWin ? @gethostbyname($_SERVER['SERVER_NAME']) : $_SERVER['SERVER_ADDR'];
        return array(
            'server_domain'          => $_SERVER['SERVER_NAME'] . "(" . $ipAddress .")",
            'server_operate_system'  => $os[0] . ' 内核版本：' . ($isWin ? $os[1] : $os[2]),
            'server_engine'          => $_SERVER['SERVER_SOFTWARE'],
            'server_hostname'        => $isWin ? $os[2] : $os[1],
            'server_flag'            => @php_uname(),
            'server_port'            => $_SERVER['SERVER_PORT'],
        );
    }

    public function getPhpParam() {
        $zend_version = zend_version();

        $routeConf = Config::get('config', 'route');

        $phpInfoUrl = 'index.php?r=home.phpInfo';
        if ($routeConf['open_fastroute']) {
            $phpInfoUrl = '/home/info';
        } else {
            if ($routeConf['use_pathinfo'] == 2) {
                $phpInfoUrl = '/home/phpInfo';
            }
        }

        return array(
            'plugin_zend_version'        => empty($zend_version) ? $this->no : $zend_version,
            'php_version'                => PHP_VERSION,
            'php_info_url'               => $phpInfoUrl,
            'php_sapi_name'              => strtoupper(php_sapi_name()),
            'php_memory_limit'           => $this->valueIsOk("memory_limit"),
            'php_safe_mode'              => $this->valueIsOk("safe_mode"),
            'php_post_max_size'          => $this->valueIsOk("post_max_size"),
            'php_upload_max_filesize'    => $this->valueIsOk("upload_max_filesize"),
            'php_precision'              => $this->valueIsOk("precision"),
            'php_max_execution_time'     => $this->valueIsOk("max_execution_time"),
            'php_default_socket_timeout' => $this->valueIsOk("default_socket_timeout"),
            'php_display_errors'         => $this->valueIsOk("display_errors"),
            'php_register_globals'       => $this->valueIsOk("register_globals"),
            'php_magic_quotes_gpc'       => $this->valueIsOk("magic_quotes_gpc"),
            'php_short_open_tag'         => $this->valueIsOk("short_open_tag"),
            'php_report_memleaks'        => $this->valueIsOk("report_memleaks"),
            'php_magic_quotes_gpc'       => $this->valueIsOk("magic_quotes_gpc"),
            'php_magic_quotes_runtime'   => $this->valueIsOk("magic_quotes_runtime"),
            'php_bcadd'                  => $this->isfun("bcadd"),
            'plugin_allow_url_fopen'     => $this->valueIsOk("allow_url_fopen"),
        );
    }

    public function getPluginParam() {
        $plugin_gd = $this->no;
        if(function_exists('gd_info')) {
            $gd_info = @gd_info();
            $plugin_gd = $gd_info["GD Version"];
        }
        return array(
            'plugin_eAccelerator'        => (phpversion('eAccelerator'))!='' ? phpversion('eAccelerator') : $this->no,
            'plugin_XCache'              => (phpversion('XCache'))!='' ? phpversion('XCache') : $this->no,
            'plugin_ftp_login'           => $this->isfun("ftp_login"),
            'plugin_xml_set_object'      => $this->isfun("xml_set_object"),
            'plugin_session_start'       => $this->isfun("session_start"),
            'php_cookie'                 => isset($_COOKIE) ? $this->ok : $this->no,
            'plugin_socket_accept'       => $this->isfun("socket_accept"),
            'php_smtp'                   => get_cfg_var("SMTP") ? $this->ok : $this->no,
            'plugin_gd'                  => $plugin_gd,
            'php_curl_init'              => $this->isfun("curl_init"),
            'plugin_gzclose'             => $this->isfun("gzclose"),
            'plugin_imap_close'          => $this->isfun("imap_close"),
            'plugin_JDToGregorian'       => $this->isfun("JDToGregorian"),
            'plugin_preg_match'          => $this->isfun("preg_match"),
            'plugin_iconv'               => $this->isfun("iconv"),
            'plugin_mb_eregi'            => $this->isfun("mb_eregi"),
            'plugin_mcrypt_cbc'          => $this->isfun("mcrypt_cbc"),
            'plugin_mhash_count'         => $this->isfun("mhash_count"),
        );
    }

    private function valueIsOk($varName) {
        switch($result = get_cfg_var($varName)) {
            case 0:
                return $this->no;
                break;
            case 1:
                return $this->ok;
                break;
            default:
                return $result;
                break;
        }
    }

    private function isfun($funName = '')
    {
        if (!$funName || trim($funName) == '' || preg_match('~[^a-z0-9\_]+~i', $funName, $tmp)) return '错误';
        return (false !== function_exists($funName)) ? $this->ok : $this->no;
    }
}


