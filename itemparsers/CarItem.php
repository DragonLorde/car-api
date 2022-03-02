<?php

use DiDom\Document;

require('dromitem.php');
require('autoruitem.php');
require('avitoitem.php');

class CarItem {

    private static $proxyLogin;
    private static $proxyPass;
    private static $proxyList;
    private static $base;

    static public function ItemCreator($url, $params , $base) {

        self::$base = $base;
        $proxyConfig = self::GetProxyConfig();
        self::$proxyLogin = $proxyConfig['login'];
        self::$proxyPass = $proxyConfig['pass'];
        self::$proxyList = self::GetProxyList();

        switch ($params) {
            case 'drom':
                Dromitem::GetDromItems($url);
                break;
            case 'auto_ru':
                Autoruitem::GetAutoRuItem($url);
                break;
            case 'avito':
                Avitoitem::GetAvitoItems($url);
                break;
            default:
                ErrorApp::Err(400, "no params");
                break;
        }
    }

    static public function GetData($url) {
        $headers = [
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
            'user-agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/'.rand(60,72).'.0.'.rand(1000,9999).'.121 Safari/537.36',
            'Cookie: gradius=200;'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_PROXY, self::$proxyList[array_rand( self::$proxyList , 1)]);     // PROXY details with port
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, self::$proxyLogin . ':' . self::$proxyPass ) ;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
        curl_setopt($ch, CURLOPT_HEADER, false);
        $html = curl_exec($ch);
        curl_close($ch);
        return $html;

    }    

    static function GetProxyConfig() {
        $stmt = self::$base->prepare("SELECT * FROM `proxy_config`");
        $stmt->execute();
        $row = $stmt->fetch();
        return $row;
    }

    static function GetProxyList() {
        $stmt = self::$base->prepare("SELECT `ip` FROM `proxy_ip`");
        $stmt->execute();
        $proxyList = [];
        while($row = $stmt->fetch()) {
            array_push($proxyList , $row['ip']);
        }
        return $proxyList;
        
    }

}