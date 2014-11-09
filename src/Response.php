<?php
namespace OrbitHttp;

class Response {
    private $content;
    private $info;
    private $error;
    private $json;
    private $dom;
    private $xpath;

    public function __construct($content, $info = null, $error = null)
    {
        $this->_setContent($content);
        $this->_setInfo($info);
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

    public function dom($utf = 0, $libxml_errors = true)
    {
        if ($this->dom == null) {
            libxml_use_internal_errors($libxml_errors);
            $doc = new \DOMDocument;
            if (!$doc->loadHTML(($utf ? '<meta http-equiv="content-type" content="text/html; charset=utf-8">' : '').$this->content)) {
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
        return json_encode($this->content);
    }

    public function obj()
    {
        return $this->json ?: $this->json = json_decode($this->content);
    }

    public function iconv($in_cod, $out_cod)
    {
        return iconv($in_cod, $out_cod, $this->content);
    }

    public function utf()
    {
        return $this->iconv('cp1251', 'utf-8');
    }

    public function get()
    {
        return $this->content;
    }

    public function __toString()
    {
        return $this->get() ?: '';
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