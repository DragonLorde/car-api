<?php


// class Prices {

//     function __construct($body)
//     {
//         $this->params = json_decode( file_get_contents("core/config/params.json"), true );
//         $this->body = $body;

//     }

//     public function GetUrls() {

//         $url1 = str_replace(" ", "", "https://" . $this->body['city'] . ".drom.ru/" . $this->body['mark'] . "/" . $this->body['model'] . "/used/?ph=1&unsold=1&order=price&" );
//         $url2 = str_replace(" ", "", "https://" . $this->body['city'] . ".drom.ru/" . $this->body['mark'] . "/" . $this->body['model'] . "/used/?ph=1&unsold=1&order_d=desc&" );
//         $url3 = str_replace(" ", "", "https://" . $this->body['city'] . ".drom.ru/" . $this->body['mark'] . "/" . $this->body['model'] . "/used/?ph=1&unsold=1&" );


//         if($this->body) {
//             $keys = array_keys( $this->body );
//         } else {
//             return array( $url1, $url2, $url3 );
//         }

//         $urlArrGen = array();
//         foreach($this->params as $key => $val) {
//             array_push($urlArrGen ,$this->MakeUrl($keys, $key));
//         }
//         $url1 = $url1 . $urlArrGen[0];
//         $url2 = $url2 . $urlArrGen[0];

//         return array( $url1 , $url2, $url3);
//     }

//     private function MakeUrl($keys , $key) {
//         $urlArr = array();
//         foreach($keys as $parms ) {
            
//             if(array_key_exists($parms , $this->params[$key]) ) {
//                 if(is_array($this->params[$key][$parms])) {
//                     array_push($urlArr, $this->params[$key][$parms][$this->body[$parms]]);
//                 } else {
//                     array_push($urlArr, $this->params[$key][$parms] . $this->body[$parms]);
//                 }
//             }
//         }
//         return implode("&" , $urlArr);
//     }
// }