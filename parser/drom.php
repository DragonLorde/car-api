<?php
// деление  = мин + ( др - мин ) /4 
use DiDom\Document;



class Drom {
    public static function CreateJson($arr) {
        $carArr = [];
        try {
            $priceValue = $arr[2];
            $links = $arr[0]->find('a[data-ftid="bulls-list_bull"]');
            foreach($links as $key => $props) {
                if($arr[2] == 1) {
                    if(!self::GetOfferPriceIndex($props)) {
                        continue;
                    }
                }
                    array_push($carArr , array(
                        'link' => $props->getAttribute('href'),
                        "price_value" => $priceValue,
                        "title" => $props->find('span[data-ftid="bull_title"]')[0]->text(),
                        "price" => $props->find('span[data-ftid="bull_price"]')[0]->text(),
                        "price_offer" => self::GetOfferPrice($props),
                        "locathion" =>  $props->find('span[data-ftid="bull_location"]')[0]->text(),
                        "date" => $props->find('div[data-ftid="bull_date"]')[0]->text(),
                        "text" =>  implode(' ', self::getText($props->find('div[data-ftid="bull_description"]'))),
                        "imgs" =>  self::getImg($props->find('div[data-ftid="bull_image]'))
                    ));
                
            }
                return $carArr;
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

    private static function GetOfferPriceIndex($props) {
        if($props->has('.ejipaoe0')) {
            $offr = $props->find('.ejipaoe0')[0]->text();
            if(strlen($offr) < 50) {
                switch ($offr) {
                    case 'хорошая цена':
                        return true;
                        break;
                    case 'отличная цена':
                        return true;
                        break;
                    case 'нормальная цена':
                        return true;
                        break;
                    default:
                        return false;
                        break;
                }
            }
        } else {
            return false;
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