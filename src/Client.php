<?php
namespace OrbitHttp;
use OrbitHttp\ConfigSet;

class Client
{
    private $cookies;
    private $configs;
    private $request_config;
    private $default_config;
    private $ch;
    private $response;

    public function __construct($config_set = ConfigSet::STANDART_REQUEST)
    {
        $this->addConfig('default', new Config(
            ConfigSet::get($config_set)
        ));
        $this->request_config = new Config();
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

    public function getCurlOpt($key)
    {
        return $this->default_config->get($key);
    }

    public function setCookieSession($name, $path = null)
    {
        if (!$this->cookies instanceof CookieSession) {
            $this->cookies = new CookieSession($name, $path);
        } else {
            $this->cookies->initSession($name, $path);
        }
    }

    public function getCookieSession()
    {
        return $this->cookies;
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
        $this->request_config->set($opt, $val);
    }

    private function _getCurlRequestOpts()
    {
        return $this->request_config;
    }

    private function _resetCurlRequestOpts()
    {
        $this->request_config->clear();
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