<?php

namespace OrbitHttp;

class ConfigSet {
    const STANDART_REQUEST = 0;
    const QUICK_REQUEST = 1;

    private static $configs = array(
        array(
            CURLOPT_AUTOREFERER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:30.0) Gecko/20100101 Firefox/30.0',
            CURLOPT_CONNECTTIMEOUT => 12,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => array()
        ),
        array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 1,
        )
    );

    public static function get($key)
    {
        return self::$configs[$key];
    }
} 