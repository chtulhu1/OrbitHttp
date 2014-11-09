<?php
namespace OrbitHttp;

class Client
{
    private static $instance;

    private $cookies;

    private $curl_options = array(
        CURLOPT_AUTOREFERER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:30.0) Gecko/20100101 Firefox/30.0',
        CURLOPT_CONNECTTIMEOUT => 12,
        CURLOPT_TIMEOUT => 12,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => array()
    );

    private $curl_request_options = array();

    private $ch;

    private function __construct(){}

    private function __clone(){}

    public static function open()
    {
        if (self::$instance === null) {
            self::$instance = new Client();
        }
        return self::$instance;
    }

    public function get($url)
    {
        return $this->_request($url);
    }

    public function post($url, $post_data)
    {
        return $this->_request($url, $post_data);
    }

    private function _request($url = null, $post_data = false)
    {
        $this->_initCurl($url);

        if (stripos($url, 'https://') === 0) {
            $this->disableSSLVerify();
        }

        $this->_buildRequestOpts();

        $this->_setCookieFiles();

        if ($post_data) {
            $this->_setPostData($post_data);
        }

        foreach ($this->_getCurlRequestOpts() as $k => $v) {
            curl_setopt($this->ch, $k, $v);
        }

        $content = curl_exec($this->ch);
        $info = curl_getinfo($this->ch);
        $error = curl_error($this->ch);
        curl_close($this->ch);
        $this->_resetCurlRequestOpts();

        if ($this->cookies) {
            CookieSession::open($this->cookies)->saveCookies();
        }

        return new Response($content, $info, $error);
    }

    private function _buildRequestOpts()
    {
        foreach ($this->getCurlOpt() as $k => $v) {
            $this->_setCurlRequestOpt($k, $v);
        }
    }


    public function setCurlOpt($opt, $value = null)
    {
        if (is_array($opt)) {
            foreach($opt as $k => $v) {
                $this->curl_options[$k] = $v;
            }
        } else {
            $this->curl_options[$opt] = $value;
        }
    }

    private function _setCurlRequestOpt($opt, $val)
    {
        $this->curl_request_options[$opt] = $val;
    }

    private function _getCurlRequestOpts()
    {
        return $this->curl_request_options;
    }

    private function _resetCurlRequestOpts()
    {
        $this->curl_request_options = array();
    }

    public function getCurlOpt($key = null)
    {
        if ($key !== null) {
            return $this->curl_options[$key];
        } else {
            return $this->curl_options;
        }
    }

    private function _initCurl($url)
    {
        $this->ch = curl_init($url);
    }

    private function _setCookieFiles()
    {
        if ($this->cookies) {
            $cookies = CookieSession::open($this->cookies);
            $cookies_file = $cookies->getFile();
            $this->_setCurlRequestOpt(CURLOPT_COOKIEFILE, $cookies_file);
            $this->_setCurlRequestOpt(CURLOPT_COOKIEJAR, $cookies_file);
        }
    }

    private function _setPostData($data)
    {
        $this->_setCurlRequestOpt(CURLOPT_POST, true);
        $this->_setCurlRequestOpt(CURLOPT_POSTFIELDS, $data);
    }

    public function setCookies($name, $path = null)
    {
        $this->cookies = $name;
        if ($name) {
            CookieSession::open($name, $path);
        }
    }

    // quick methods
    public function setProxy($proxy, $type = CURLPROXY_HTTP, $auth = false)
    {
        $this->setCurlOpt(CURLOPT_PROXY, $proxy);
        $this->setCurlOpt(CURLOPT_PROXYTYPE, $type);
        if ($auth) {
            $this->setCurlOpt(CURLOPT_PROXYUSERPWD, $auth);
        }
    }

    public function getProxy()
    {
        return $this->getCurlOpt(CURLOPT_PROXY);
    }

    public function setConnectTimeout($sec)
    {
        $this->setCurlOpt(CURLOPT_CONNECTTIMEOUT, $sec);
    }

    public function getConnectTimeout()
    {
        return $this->getCurlOpt(CURLOPT_CONNECTTIMEOUT);
    }

    public function setTimeout($sec)
    {
        $this->setCurlOpt(CURLOPT_TIMEOUT, $sec);
    }

    public function getTimeout()
    {
        return $this->getCurlOpt(CURLOPT_TIMEOUT);
    }

    public function setBrowser($ua)
    {
        $this->setCurlOpt(CURLOPT_USERAGENT, $ua);
    }

    public function getBrowser()
    {
        return $this->getCurlOpt(CURLOPT_USERAGENT);
    }

    public function setHeaders(array $headers)
    {
        $this->setCurlOpt(CURLOPT_HTTPHEADER, $headers);
    }

    public function setReferer($referer)
    {
        $this->_setCurlRequestOpt(CURLOPT_REFERER, $referer);
    }

    public function disableSSLVerify()
    {
        $this->_setCurlRequestOpt(CURLOPT_SSL_VERIFYPEER, 0);
        $this->_setCurlRequestOpt(CURLOPT_SSL_VERIFYHOST, 0);
    }
} 