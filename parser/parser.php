<?php


use DiDom\Document;


class Parsing {

    public $body;
    public $method;
    public $params;
    public $proxyList;
    public $proxyLogin;
    public $proxyPass;
    public $base;

    function __construct($body, $method, $base) {
        $this->body = $body;
        $this->method = $method;
        $this->params = json_decode( file_get_contents("core/config/params.json"), true );
        $this->base = $base;
        $proxyConfig = $this->GetProxyConfig();
        $this->proxyLogin = $proxyConfig['login'];
        $this->proxyPass = $proxyConfig['pass'];
        $this->proxyList = $this->GetProxyList();
    }

    private function GetProxyConfig() {
        $stmt = $this->base->prepare("SELECT * FROM `proxy_config`");
        $stmt->execute();
        $row = $stmt->fetch();
        return $row;
    }

    private function GetProxyList() {
        $stmt = $this->base->prepare("SELECT `ip` FROM `proxy_ip`");
        $stmt->execute();
        $proxyList = [];
        while($row = $stmt->fetch()) {
            array_push($proxyList , $row['ip']);
        }
        return $proxyList;
        
    }

    private function MakeUrl($keys , $key) {
        $urlArr = array();
        foreach($keys as $parms ) {
            if(is_array($this->params[$key][$parms])) {
                array_push($urlArr, $this->params[$key][$parms][$this->body[$parms]]);
            } else {
                array_push($urlArr, $this->params[$key][$parms] . $this->body[$parms]);
            }
        }
        return implode("&" , $urlArr);
    }

    private function CachExist($urlArr ) {
        $docArr = [];
        foreach($urlArr as $key ) {
            $doc = Cache::getCache($key);
            if ( $doc ) {
                array_push( $docArr,  $doc);
            } else {
                return false;
            }
        }
        return $docArr;
        
    }

    private function CachClear($urlArr ) {
        $docArr = [];
        foreach($urlArr as $key ) {
            $doc = Cache::ClearCash($key);
            if ( $doc ) {
                array_push( $docArr,  $doc);
            } else {
                return false;
            }
        }
        return $docArr;
        
    }

    private function GetUrls() {

        if(!empty($this->method[3])) {
            $url1 = str_replace(" ", "", "https://" . $this->method[1] . ".drom.ru/" . $this->method[2] . "/" . $this->method[3] . "/used/?ph=1&unsold=1&" );
            $url2 = str_replace(" ", "",  "https://auto.ru/". $this->method[1] ."/cars/" .  $this->method[2] . "/" . $this->method[3] . "/used/?" );
            $url3 =  str_replace(" ", "", "https://www.avito.ru/" . $this->method[1] . "/avtomobili/" . $this->method[2] . "/" . $this->method[3] . "?radius=0&s=104");
        } else {
            $url1 = str_replace(" ", "", "https://" . $this->method[1] . ".drom.ru/" . $this->method[2] .   "/used/?ph=1&unsold=1&" );
            $url2 = str_replace(" ", "",  "https://auto.ru/". $this->method[1] ."/cars/" .  $this->method[2] .  "/used/?" );
            $url3 =  str_replace(" ", "", "https://www.avito.ru/" . $this->method[1] . "/avtomobili/" . $this->method[2] . "?radius=0&s=104");
        }

        if($this->body) {
            $keys = array_keys( $this->body );
        } else {
            return array($url2 ,$url1, $url3);
        }

        $urlArrGen = array();
        foreach($this->params as $key => $val) {
            array_push($urlArrGen ,$this->MakeUrl($keys, $key));
        }
        $url1 = $url1 . $urlArrGen[0];
        $url2 = $url2 . $urlArrGen[1];
        return array( $url2, $url1, $url3 );
    }

    private function MakeAvitoUrl($url) {
        $ch = curl_init();

        $headers = [
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
            'user-agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/'.rand(60,72).'.0.'.rand(1000,9999).'.121 Safari/537.36',
            'Cookie: gradius=200;'
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
            
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }
        
        curl_setopt($ch, CURLOPT_PROXY, $this->proxyList[array_rand( $this->proxyList , 1)]);     // PROXY details with port
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyLogin . ':' . $this->proxyPass ) ;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_exec($ch);
        $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); 
        return $url;
    }

    private function GetUrlsPages() {

        if(!empty($this->method[4])) {
            $url1 = str_replace(" ", "", "https://" . $this->method[1] . ".drom.ru/" . $this->method[2] . "/" . $this->method[3] . "/used" . '/page' .  $this->method[4] . "?ph=1&unsold=1&");
            $url2 = str_replace(" ", "",  "https://auto.ru/". $this->method[1] ."/cars/" .  $this->method[2] . "/" . $this->method[3] . "/used/?page=" . $this->method[4] . '&sort=cr_date-desc&');
            $url3_p =  str_replace(" ", "", "https://www.avito.ru/" . $this->method[1] . "/avtomobili/" . $this->method[2] . "/" . $this->method[3] . "?localPriority=1&radius=0&s=104&p=" . $this->method[4] . '&' );
            $url3 = $this->MakeAvitoUrl($url3_p);
            $prg_url = preg_replace('/\\?.*/', '', $url3);
            $url3 = $prg_url . "?localPriority=1&p=" . $this->method[4] . "&radius=0&s=104";
        } else {
            $url1 = str_replace(" ", "", "https://" . $this->method[1] . ".drom.ru/" . $this->method[2] . "/used" .  '/page' .  $this->method[3] .  "?ph=1&unsold=1&" );
            $url2 = str_replace(" ", "",  "https://auto.ru/". $this->method[1] ."/cars/" .  $this->method[2] .  "/used/?page=" . $this->method[3] . '&sort=cr_date-desc&');
            $url3_p =  str_replace(" ", "", "https://www.avito.ru/" . $this->method[1] . "/avtomobili/" . $this->method[2] . "?localPriority=1&radius=0&s=104&p=" . $this->method[3]);
            $url3 = $this->MakeAvitoUrl($url3_p);
            $url3 = $url3 . "?&p=" . $this->method[4];
        }
        
        if($this->body) {
            $keys = array_keys( $this->body );
        } else {
            return array($url2 ,$url1, $url3);
        }

        $urlArrGen = array();
        foreach($this->params as $key => $val) {
            array_push($urlArrGen ,$this->MakeUrl($keys, $key));
        }
        $url1 = $url1 . $urlArrGen[0];
        $url2 = $url2 . $urlArrGen[1];
        return array( $url2, $url1, $url3 );
    }

    private function SortArray($arr) {
        $sortArr = [];
        
        for($i2 = 0; $i2 < count($arr[2]); $i2++ ) {
            array_push($sortArr, $arr[0][$i2] );
            array_push($sortArr, $arr[1][$i2] );
            array_push($sortArr, $arr[2][$i2] );
        }
        
        return $sortArr;
    }

    private function GetNumberPage($doc) {
        if($doc->has('.css-o6xugi')) {
            $nmbPage = $doc->find('.css-o6xugi')[0]->text();
            return $nmbPage;
        } else {
            return false;
        }
    }

    private function BuildUrl($arr , $urls, $doc) {
        $carArr = array(
            "url" => $urls,
            "pageNumber" => $this->GetNumberPage($doc),
            "data" => ""
        );
        $res = $this->SortArray($arr);
        $carArr['data'] = $res;
        echo json_encode( $carArr , JSON_UNESCAPED_UNICODE);
        exit();
    }


    private function CashCheck($urls) {
        
        $cash = $this->CachExist($urls);
        if($cash) {
            $carArr = [];
            $drom = new Document ($cash[1],  false, 'windows-1251');
            $auto_ru = new Document ($cash[0],  false);
            $avito = new Document($cash[2] , false);
            /////////////
            array_push($carArr, Auto_ru::CreateJson([$auto_ru , $urls, $this->body['price_value'] ] , $carArr));
            array_push($carArr , Drom::CreateJson([ $drom , $urls, $this->body['price_value'] ] , $carArr));
            array_push($carArr, Avito::CreateJson([$avito , $urls, $this->body['price_value'] ] , $carArr ));
            $this->BuildUrl($carArr , $urls , $drom);
            return false;
        }
        return true;
    }

    private function GetJson($cash , $urls) {

        $carArr = [];
        $drom = new Document ($cash[1],  false, 'windows-1251');
        $auto_ru = new Document ($cash[0],  false);
        $avito = new Document($cash[2] , false);
        /////////////
        array_push($carArr, Auto_ru::CreateJson([$auto_ru , $urls] , $carArr));
        array_push($carArr , Drom::CreateJson([ $drom , $urls ] , $carArr));
        array_push($carArr, Avito::CreateJson([$avito , $urls] , $carArr));
        $this->BuildUrl($carArr , $urls , $drom);
        
    }

    public function GetData() {

        $urls = $this->GetUrlsPages();


        if($this->CashCheck($urls) == false) {
           return false;
        }
        
        $headers = [
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
            'user-agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/'.rand(60,72).'.0.'.rand(1000,9999).'.121 Safari/537.36',
            'Cookie: gradius=100;'
        ];

        $multi = curl_multi_init();
        $channels = array();
        foreach ($urls as $url) {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            
            if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_PROXY, $this->proxyList[array_rand( $this->proxyList , 1)]);     // PROXY details with port
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyLogin . ':' . $this->proxyPass ) ;
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
            curl_setopt($ch, CURLOPT_HEADER, false);

            curl_multi_add_handle($multi, $ch);
            
            $channels[$url] = $ch;
        }
         
        $active = null;
        do {
            $mrc = curl_multi_exec($multi, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
         
        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($multi) == -1) {
                continue;
            }
        
            do {
                $mrc = curl_multi_exec($multi, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
        $i = 0;
        $cashArr = [];
        foreach ($channels as $channel) {
            $html = curl_multi_getcontent($channel);
            if( !empty($html) ) {
                Cache::setCache( $html, $urls[$i] );
                array_push($cashArr, $html);
            }

            curl_multi_remove_handle($multi, $channel);
            $i++;
        }
        curl_multi_close($multi);
        $this->GetJson($cashArr , $urls);
    }
    
    public function GetDataFeed($city , $id) {

        
        $urls = [
            str_replace(" ", "", 'https://auto.ru/' . $city . '/cars/used/?sort=cr_date-desc&&seller_group=PRIVATE&page=' . $id),
            str_replace(" ", "", 'https://' . $city . '.drom.ru/auto/used/all/page' . $id . '/?owner_type=1' ),
            str_replace(" ", "", 'https://www.avito.ru/' . $city . '/avtomobili?localPriority=1&cd=1&radius=100&s=104&&user=1&p=' . $id ),
            
        ];

        if($this->CashCheck($urls) == false) {
           return false;
        }
        

        $headers = [
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
            'user-agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/'.rand(60,72).'.0.'.rand(1000,9999).'.121 Safari/537.36',
            'Cookie: gradius=100;'
        ];

        $multi = curl_multi_init();
        $channels = array();
        foreach ($urls as $url) {

            $ch = curl_init();
            if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_PROXY, $this->proxyList[array_rand( $this->proxyList , 1)]);     // PROXY details with port
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyLogin . ':' . $this->proxyPass ) ;
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
            curl_setopt($ch, CURLOPT_HEADER, false);

            curl_multi_add_handle($multi, $ch);
            
            $channels[$url] = $ch;
        }
         
        $active = null;
        do {
            $mrc = curl_multi_exec($multi, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
         
        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($multi) == -1) {
                continue;
            }
        
            do {
                $mrc = curl_multi_exec($multi, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
        $i = 0;
        $cashArr = [];
        foreach ($channels as $channel) {
            $html = curl_multi_getcontent($channel);
            if( !empty($html) ) {
                Cache::setCache( $html, $urls[$i] );
                array_push($cashArr, $html);
            }

            curl_multi_remove_handle($multi, $channel);
            $i++;
        }
        curl_multi_close($multi);
        $this->GetJson($cashArr , $urls);
    }

    public function Refresh() {
        $urls = $this->GetUrlsPages();
        $this->CachClear($urls );

        $this->GetData();

    }
    
    public function Feed($id) {
        $city = $this->GetCity();
        $this->GetDataFeed($city , $id);
    }

    function GetCity() {
        $ip = $this->getIp();
        $SxGeo = new SxGeo('SxGeoCity.dat', SXGEO_BATCH | SXGEO_MEMORY);
        $res = $SxGeo->get($ip);
        $city = $res['city']['name_en'];
        unset($SxGeo);
        return !empty($city) ? mb_strtolower($city) : "tyumen";

    }

    function getIp() {
        $keys = [
          'HTTP_CLIENT_IP',
          'HTTP_X_FORWARDED_FOR',
          'REMOTE_ADDR'
        ];
        foreach ($keys as $key) {
          if (!empty($_SERVER[$key])) {
            $ip = trim(end(explode(',', $_SERVER[$key])));
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
              return $ip;
            }
          }
        }
    }
    
}