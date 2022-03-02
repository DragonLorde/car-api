<?php


class Cache {

    static $cashDir = 'cash';

    static $cashDirJson = 'cash';


    static public function setCache($content, $cacheId)
    {
        if ($content == '') {
            return ;
        }
        $fileName = self::$cashDir.'/'.md5($cacheId);
        if (!file_exists(self::$cashDir)) {
            mkdir(self::$cashDir, 0777);
        }
        $f = fopen($fileName , 'w+');
        fwrite($f, $content);
        fclose($f);
    }

    static public function getCache($cacheId, $cashExpired=true, &$fileName='' , $cashTime=100 )
    {
        if (!$cashExpired) {
            return ;
        }
        $fileName = self::$cashDir.'/'.md5($cacheId);
        if (!file_exists($fileName)) {
            return false;
        }
        $time = time() - filemtime($fileName);
        
        if ($time  > $cashTime) {
            return false;
        }
        return file_get_contents($fileName);
    }

    static public function ClearCash($cacheId, $cashExpired=true, &$fileName='' , $cashTime=15 )
    {
        if (!$cashExpired) {
            return ;
        }
        $fileName = self::$cashDir.'/'.md5($cacheId);
        if (!file_exists($fileName)) {
            return false;
        }
        $time = time() - filemtime($fileName);
        
        if ($time  > $cashTime) {
            unlink($fileName);
        }
    }
    
}