<?php
use DiDom\Document;

class PhoneParser {
    static function Parser($phone) {
        $url = 'https://' . $phone . '.drom.ru/';
        $data = PhoneItem::GetData($url);
        $drom = new Document ($data,  false, 'windows-1251');
        self::CreateJson($drom);
    }

    public static function CreateJson($data) {
        $carArr = array(
            "data" => [],
            "cars" => [],
        );
        try {
            $links = $data->find('a[data-ftid="bulls-list_bull"]');
            foreach($links as $key => $props) {
                    array_push($carArr['cars'] , array(
                        'link' => $props->getAttribute('href'),
                        "title" => $props->find('span[data-ftid="bull_title"]')[0]->text(),
                        "price" => $props->find('span[data-ftid="bull_price"]')[0]->text(),
                        "price_offer" => self::GetOfferPrice($props),
                        "locathion" =>  $props->find('span[data-ftid="bull_location"]')[0]->text(),
                        "date" => $props->find('div[data-ftid="bull_date"]')[0]->text(),
                        "text" =>  implode(' ', self::getText($props->find('div[data-ftid="bull_description"]'))),
                        "imgs" =>  self::getImg($props->find('div[data-ftid="bull_image]'))
                    ));
                
            }
                $first = $carArr['cars'][0]['date'];
                $last = $carArr['cars'][count($carArr['cars'])-1]['date'];
                $carArr['data']['first'] = $first;
                $carArr['data']['last'] = $last;
                $carArr['data']['owner_count'] = count($carArr['cars']);
                $carArr['data']['cars_count'] = count($carArr['cars']);
                echo json_encode($carArr, JSON_UNESCAPED_UNICODE);
        } catch (Error $e) {
            ErrorApp::Err(400 , $e);
        }
    }

    private static function GetOfferPrice($props) {
        if($props->has('.ejipaoe0')) {
            $offr = $props->find('.ejipaoe0')[0]->text();
            if(strlen($offr) < 40) {
                return $offr;
            }
        }
    }


    private static function getText($doc) {
        $textArr = [];
        foreach($doc as $key => $prop) {
            foreach($prop->find('span[data-ftid="bull_description-item"]') as $key2 => $prop2) {
                array_push($textArr, $prop2->text());
            }
        }
        return $textArr;
    }

    private static function getImg($doc) {
        //data-ftid="bull_image"
        $imgArr = [];
        foreach($doc as $key => $prop) {
            foreach($prop->find('img') as $key2 => $prop2) {
                if ($prop2->getAttribute('data-src') != null) {
                    array_push($imgArr, 
                        $prop2->getAttribute('data-src')
                    );
                }
            }
        }
        return $imgArr;
    }
}