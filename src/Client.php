<?php
namespace OrbitHttp;

class Client
{
    private $cookies;
    private $configs;
    private $default_options = array(
        CURLOPT_AUTOREFERER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => 1,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:30.0) Gecko/20100101 Firefox/30.0',
        CURLOPT_CONNECTTIMEOUT => 12,
        CURLOPT_TIMEOUT => 12,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => array()
    );
    private $one_time_config;
    private $default_config;
    private $ch;
    private $response;

    public function __construct()
    {
        $this->addConfig('default', new Config());
        $this->one_time_config = new Config();
        $this->setCurlOpt($this->default_options);
    }

    public function addConfig($name, Config $config)
    {
        if ($name != '') {
            $this->configs[$name] = $config;
            $this->useConfig($name);
        }
    }

    public function useConfig($name)
    {
        if (isset($this->configs[$name]))
            $this->default_config = $this->configs[$name];
    }

    public function setCurlOpt($opt, $value = null)
    {
        if (is_array($opt)) {
            foreach($opt as $k => $v) {
                $this->default_config->set($k, $v);
            }
        } else {
            $this->default_config->set($opt, $value);
        }
    }

    public function response()
    {
        return $this->response instanceof Response ? $this->response : null;
    }

    public function getCurlOpt($key)
    {
        return $this->default_config->get($key);
    }

    public function setCookies($name, $path = null)
    {
        if (!$this->cookies instanceof CookieSession) {
            $this->cookies = new CookieSession($name, $path);
        } else {
            $this->cookies->initSession($name, $path);
        }
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

        $this->_buildRequestOptions();

        if ($this->cookies instanceof CookieSession) {
            $cookies_file = $this->cookies->getFile();
            $this->_setCurlRequestOpt(CURLOPT_COOKIEFILE, $cookies_file);
            $this->_setCurlRequestOpt(CURLOPT_COOKIEJAR, $cookies_file);
        }

        if ($post_data) {
            $this->_setPostData($post_data);
        }

        foreach ($this->_getCurlRequestOpts() as $k => $v) {
            curl_setopt($this->ch, $k, $v);
        }

        $content = curl_exec($this->ch);
        $headers = null;
        if ($this->getCurlOpt(CURLOPT_HEADER))
        {
            $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
            $headers = substr($content, 0, $header_size);
            $content = substr($content, $header_size);
        }
        $info = curl_getinfo($this->ch);
        $error = curl_error($this->ch);
        curl_close($this->ch);
        $this->_resetCurlRequestOpts();

        if ($this->cookies instanceof CookieSession) {
            $this->cookies->saveCookies();
        }

        return $this->response = new Response($content, $info, $headers, $error);
    }

    private function _buildRequestOptions()
    {
        foreach ($this->default_config as $k => $v) {
            $this->_setCurlRequestOpt($k, $v);
        }
    }

    private function _setCurlRequestOpt($opt, $val)
    {
        $this->one_time_config->set($opt, $val);
    }

    private function _getCurlRequestOpts()
    {
        return $this->one_time_config;
    }

    private function _resetCurlRequestOpts()
    {
        $this->one_time_config->clear();
    }

    private function _initCurl($url)
    {
        $this->ch = curl_init($url);
    }

    private function _setPostData($data)
    {
        $this->_setCurlRequestOpt(CURLOPT_POST, true);
        $this->_setCurlRequestOpt(CURLOPT_POSTFIELDS, $data);
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