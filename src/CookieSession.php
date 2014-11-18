<?php
namespace OrbitHttp;

class CookieSession
{
    private $cookies = array();

    private $active_cookie = null;

    public function __construct($name, $path = null)
    {
        $this->initSession($name, $path);
    }

    public function __destruct()
    {
        if ($this->isTmp()) {
            $this->_deleteFile();
        }
    }

    public function initSession($name, $path = null)
    {
        $this->useSession($name);
        if ($path != null) {
            $this->_setCookies('path', $path);
            if ($this->isTmp() == false) {
                $this->_setCookies('file', $path);
            }
        }
    }

    public function useSession($name)
    {
        $this->active_cookie = $name;
    }

    public function getFile()
    {
        if ($this->isTmp()) {
            $filename = rtrim($this->_getCookie('path'), '/') . '/' . $this->active_cookie . '_' . strftime('%d_%m_%Y_%H_%M_%S') . '.txt';
            $this->_setCookies('file', $filename);
        }

        if ($this->_getCookie('val')) {
            file_put_contents($this->_getCookie('file'), $this->_getCookie('val'));
        }

        return $this->_getCookie('file');
    }

    public function saveCookies()
    {
        if ($this->_getCookie('file') && file_exists($this->_getCookie('file'))) {
            $this->_setCookies('val', file_get_contents($this->_getCookie('file')));
        }
    }

    private function _deleteFile()
    {
        @unlink($this->_getCookie('file'));
    }

    public function isTmp()
    {
        if ($this->_getCookie('path') == null) {
            return false;
        } else {
            return filetype($this->_getCookie('path')) == 'dir' ? true : false;
        }
    }

    private function _setCookies($cookie, $val = null)
    {
        if (is_array($cookie)) {
            $this->cookies[$this->active_cookie] = $cookie;
        } else {
            $this->cookies[$this->active_cookie][$cookie] = $val;
        }
    }

    private function _getCookie($key = null)
    {
        return isset($this->cookies[$this->active_cookie][$key]) ? $this->cookies[$this->active_cookie][$key] : null;
    }
} 