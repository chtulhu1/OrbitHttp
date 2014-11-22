<?php
namespace OrbitHttp;

class Response {
    private $content;
    private $headers;
    private $cookies;
    private $info;
    private $error;
    private $json;
    private $dom;
    private $xpath;

    public function __construct($content, $info = null, $headers = null, $error = null)
    {
        $this->_setContent($content);
        $this->_setInfo($info);
        $this->_setHeaders($headers);
        $this->_setError($error);
    }

    private function _setContent($content)
    {
        $this->content = $content;
    }

    private function _setInfo($info)
    {
        $this->info = $info;
    }

    private function _setError($error)
    {
        $this->error = $error;
    }

    private function _setHeaders($headers)
    {
        foreach (explode("\n", $headers) as $h) {
            if (strpos($h, ":")) {
                list($name, $val) = explode(":", $h, 2);
                if ($name == 'Set-Cookie') {
                   foreach (explode(';', $val) as $cookies) {
                       if (strpos($cookies, '=')) {
                           list($cname, $cval) = explode("=", $cookies);
                           $this->cookies[trim($cname)] = trim(urldecode($cval));
                       }
                   }
                }
            }
        }
        $this->headers = $headers;
    }

    public function getCookies($key)
    {
        return $this->cookies[$key];
    }

    public function headers()
    {
        return $this->headers;
    }

    public function dom($utf = 0, $libxml_errors = true)
    {
        if ($this->dom == null) {
            libxml_use_internal_errors($libxml_errors);
            $doc = new \DOMDocument;
            if (!$doc->loadHTML(($utf ? '<meta http-equiv="content-type" content="text/html; charset=utf-8">' : '').$this->body())) {
                throw new \Exception('cannot load dom');
            } else {
                $this->dom = $doc;
            }
        }
        return $this->dom;
    }

    public function xpath($utf = 0, $libxml_errors = true)
    {
        if (!$this->xpath) {
            if ($doc = $this->dom($utf, $libxml_errors)) {
                $this->xpath = new \DOMXpath($doc);
            }
        }
        return $this->xpath;
    }

    public function json()
    {
        return json_encode($this->body());
    }

    public function obj()
    {
        return $this->json ?: $this->json = json_decode($this->body());
    }

    public function iconv($in_cod, $out_cod)
    {
        return iconv($in_cod, $out_cod, $this->body());
    }

    public function utf()
    {
        return $this->iconv('cp1251', 'utf-8');
    }

    public function body()
    {
        return $this->content;
    }

    public function __toString()
    {
        return (string) $this->body();
    }

    public function info($key = null)
    {
        if ($key) {
            return $this->info[$key];
        } else {
            return $this->info;
        }
    }

    public function dump()
    {
        print_r($this->info);
    }
} 