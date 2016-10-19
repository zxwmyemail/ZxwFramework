<?php
class HttpCURL{

    const DEFAULT_PORT = 80;
    const DEFAULT_HEADER = 0;
    const DEFAULT_TIMEOUT = 30;
    private $args;
    private $ch;

    public function __construct(stdClass $args = null){
        if(!isset($args)){
            $args = new stdClass();
            $args->header = self::DEFAULT_HEADER;
            $args->port = self::DEFAULT_PORT;
            $args->timeout = self::DEFAULT_TIMEOUT;
        }
        $this->args = $args;
        $this->ch = curl_init();
    }


    public function __destruct(){
        curl_close($this->ch);
    }

    public function http_get($url, $cookie = false){
        if($cookie){
            curl_setopt($this->ch, CURLOPT_COOKIE, $cookie);
        }

        curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_HEADER, $this->args->header);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->args->timeout);
        curl_setopt($this->ch, CURLOPT_PORT, $this->args->port);
        // curl_setopt($this->ch, CURLOPT_VERBOSE, 1);
        $data = curl_exec($this->ch);
        $errno = curl_errno($this->ch);
        if($errno){
            $error = curl_error($this->ch);
            throw new Exception($error, $errno);
        }
        $http_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        if($http_code != 200){
            if($http_code == 301 || $http_code == 302){
                $dt = $this->curl_redir_exec($this->ch, $url);
                if($dt){
                    return $dt;
                } 
            }
            throw new Exception("HTTP Code=$http_code", $http_code);  
        }
        return $data;
    }

    public function http_post($url, $post_data, $cookie = false){
        if($cookie){
            curl_setopt($this->ch, CURLOPT_COOKIE, $cookie);
        }

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $post_data); // $post_data  string or hash array
        curl_setopt($this->ch, CURLOPT_HEADER, $this->args->header);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->args->timeout);
        curl_setopt($this->ch, CURLOPT_PORT, $this->args->port);
        // curl_setopt($this->ch, CURLOPT_VERBOSE, 1);
        $data = curl_exec($this->ch);
        $errno = curl_errno($this->ch);
        if($errno){
            $error = curl_error($this->ch);
            throw new Exception($error, $errno);
        }
        $http_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        if($http_code != 200){
            if($http_code == 301 || $http_code == 302){
                $dt = $this->curl_redir_exec($this->ch, $url);
                if($dt){
                    return $dt;
                } 
            }
            throw new Exception("HTTP Code=$http_code", $http_code);  
        }
        return $data;
    }

    public function curl_redir_exec($ch, $url){
        static $curl_loops = 0;
        static $curl_max_loops = 5;
        $useragent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)";
        if ($curl_loops++ >= $curl_max_loops) {
            $curl_loops = 0;
            return false;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        $data = curl_exec($ch);
        $ret = $data;
        list($header, $data) = explode("\r\n\r\n", $data, 2);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $last_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        if ($http_code == 301 || $http_code == 302) {
            $matches = array();
            preg_match('/Location:(.*?)\n/', $header, $matches);
            $url = @parse_url(trim(array_pop($matches)));
            if (!$url) {
                $curl_loops = 0;
                return $data;
            }
            $new_url = $url['scheme'] . '://' . $url['host'] . $url['path']. (isset($url['query']) ? '?' . $url['query'] : '');
            $new_url = stripslashes($new_url);
            return curl_get_file_contents($new_url, $last_url);
        } else {
            $curl_loops = 0;
            list($header, $data) = explode("\r\n\r\n", $ret, 2);
            return $data;
        }
    }
}
