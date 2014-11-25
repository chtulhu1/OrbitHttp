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
        $responseHeaders = array();
        foreach (explode("\n", $headers) as $h) {
            if (strpos($h, ":")) {
                list($name, $val) = explode(":", $h, 2);
                if ($name == 'Set-Cookie') {
                    $cookies = substr($val, 0, strpos($val, ';'));
                    if (strpos($cookies, '=')) {
                        list($cname, $cval) = explode("=", $cookies);
                        $this->cookies[trim($cname)] = trim(urldecode($cval));
                    }
                } else {
                    $responseHeaders[$name][] = trim(urldecode($val));
                }
            }
        }
        $this->headers = $responseHeaders;
    }

    public function getHeaders($key = null)
    {
        if ($key !== null) {
            return array_key_exists($key, $this->headers) ? $this->headers[$key] : null;
        }
        return $this->headers;
    }

    public function getCookies($key = null)
    {
        if ($key !== null) {
            return array_key_exists($key, $this->cookies) ? $this->cookies[$key] : null;
        }
        return $this->cookies;
    }

    public function getDOM($utf = 0, $libxml_errors = true)
    {
        if ($this->dom == null) {
            libxml_use_internal_errors($libxml_errors);
            $doc = new \DOMDocument;
            if (!$doc->loadHTML(($utf ? '<meta http-equiv="content-type" content="text/html; charset=utf-8">' : '').$this->getBody())) {
                throw new \Exception('cannot load dom');
            } else {
                $this->dom = $doc;
            }
        }
        return $this->dom;
    }

    public function getXPath($utf = 0, $libxml_errors = true)
    {
        if (!$this->xpath) {
            if ($doc = $this->getDOM($utf, $libxml_errors)) {
                $this->xpath = new \DOMXpath($doc);
            }
        }
        return $this->xpath;
    }

    public function getObj()
    {
        return $this->json ?: $this->json = json_decode($this->body());
    }

    public function iconv($in_cod, $out_cod)
    {
        return iconv($in_cod, $out_cod, $this->body());
    }

    public function toUtf()
    {
        return $this->iconv('cp1251', 'utf-8');
    }

    public function getBody()
    {
        return $this->content;
    }

    public function __toString()
    {
        return (string) $this->getBody();
    }

    public function getInfo($key = null)
    {
        return $key !== null && array_key_exists($key, $this->info) ? $this->info[$key] : $this->info;
    }

    public function dump()
    {
        print_r($this->info);
    }
} 