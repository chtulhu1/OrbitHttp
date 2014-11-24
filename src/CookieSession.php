<?php
namespace OrbitHttp;

class CookieSession
{
    private $cookie_sessions = array();
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
            $this->setCookie('path', $path);
            if ($this->isTmp() == false) {
                $this->setCookie('file', $path);
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
            $filename = rtrim($this->getCookie('path'), '/')
                . '/' . $this->active_cookie . '_'
                . strftime('%d_%m_%Y_%H_%M_%S') . '.txt';

            $this->setCookie('file', $filename);
        }

        if ($this->getCookie('val')) {
            file_put_contents($this->getCookie('file'), $this->getCookie('val'));
        }

        return $this->getCookie('file');
    }

    public function saveCookies()
    {
        if ($this->getCookie('file') && file_exists($this->getCookie('file'))) {
            $this->setCookie('val', file_get_contents($this->getCookie('file')));
        }
    }

    private function _deleteFile()
    {
        @unlink($this->getCookie('file'));
    }

    public function isTmp()
    {
        if ($this->getCookie('path') == null) {
            return false;
        } else {
            return filetype($this->getCookie('path')) == 'dir' ? true : false;
        }
    }

    public function setCookie($cookie, $val = null)
    {
        if (is_array($cookie)) {
            foreach ($cookie as $k => $v) {
                $this->setCookie($k, $v);
            }
        } else {
            $this->cookie_sessions[$this->active_cookie][$cookie] = $val;
        }
    }

    public function getCookie($key = null)
    {
        if ($key !== null) {
            return array_key_exists($key, $this->cookie_sessions[$this->active_cookie])
                ? $this->cookie_sessions[$this->active_cookie][$key]
                : null;
        }
        return $this->cookie_sessions[$this->active_cookie];
    }
} 