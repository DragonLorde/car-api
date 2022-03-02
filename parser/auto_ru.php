<?php

use DiDom\Document;



class Auto_ru {

    public static function CreateJson($arr ) {
        $carArr = [];
        try {
            $priceValue = $arr[2];
            $links = $arr[0]->find('.ListingItem');
            foreach($links as $key => $props) {
                $priceValue;
                if($priceValue == 1) {
                    if(!self::GetOfferPriceIndex($props)) {
                        continue;
                    }
                }
                array_push($carArr , array(
                            "link" => $props->find('a')[0]->getAttribute('href'),
                            "title" => $props->find('.ListingItemTitle__link')[0]->text(), 
                            "price" => self::GetPrice($props),
                            "price_offer" => self::GetPriceOffer($props),
                            "locathion" => self::GetLoc($props),
                            "date" => self::GetDate($props),
                            "text" => implode(" " , self::getText($props)),
                            "imgs" => self::getImg($props),
                            
                         
                        ));
            }
            // $carArr['url'] = $arr[1];           
            return $carArr;
        } catch (Error $e) {
            //echo $e;
            ErrorApp::Err(400 , $e);
        }
    }

    private static function GetOfferPriceIndex($props) {
        if($props->has('.OfferPriceBadge')) {
            $offr = $props->find('.OfferPriceBadge')[0]->text();
                switch ($offr) {
                    case 'Отличная цена':
                        return true;
                        break;
                    case 'Хорошая цена':
                        return true;
                        break;
                    case 'нормальная цена':
                        return true;
                        break;
                    default:
                        return false;
                        break;
                }
            
        } else {
            return false;
        }
    }

    private static function GetDate($props) {
        if($props->has('.MetroListPlace__content ')) {
            return $props->find('.MetroListPlace__content ')[0]->text();
        }
    }

    private static function GetLoc($props) {
        if($props->has('.MetroListPlace__regionName')) {
            return $props->find('.MetroListPlace__regionName')[0]->text();
        }
    }

    private static function GetPrice($props) {
        //var_dump($props->find('.ListingItemPrice-module__container'));
        if($props->has('.ListingItemPrice__link')) { 
            return $props->find('.ListingItemPrice__link')[0]->text();
        } else if($props->has('.ListingItemPrice__content')) {
            return $props->find('.ListingItemPrice__content')[0]->text();
        }
    }

    private static function GetPriceOffer($props) {
        //var_dump($props->find('.ListingItemPrice-module__container'));
        if($props->has('.OfferPriceBadge  ')) { 
            return $props->find('.OfferPriceBadge ')[0]->text();
        }
    }

    private static function getText($props) {
        $textArr = [];
        $texts = $props->find('.ListingItemTechSummaryDesktop__cell');
        
        foreach($texts as $key2 => $prop2) {
            array_push($textArr, $prop2->text());
        }
        return $textArr;
    }


    private static function getImg($props) {
        $imgArr = [];
        if($props->has('.Brazzers')) {
            if($props->has('.Brazzers__image')) {
                $imgs = $props->find('.Brazzers__image ');
                foreach($imgs as $propsImg) {
                    array_push($imgArr ,  $propsImg->getAttribute('data-src') );
                }
            } else if($props->has('.LazyImage__image')) {
                array_push($imgArr, $props->find('.LazyImage__image')[0]->getAttribute('src') );
            }
        } 
            return $imgArr;
    }
}