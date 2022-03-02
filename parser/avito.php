<?php

use DiDom\Document;



class Avito {

    public static function CreateJson($arr ) {
        $carArr = [];
        try {
            $priceValue = $arr[2];
            $links = $arr[0]->find('div[data-marker="item"]');
            foreach($links as $key => $props) {
                if($priceValue == 1) {
                    if(!self::GetOfferPriceIndex($props)) {
                        continue;
                    }
                }

                array_push($carArr , array(
                    "link" => "https://www.avito.ru" . $props->find('a')[0]->getAttribute('href'),
                    "title" => $props->find('h3[itemprop="name"]')[0]->text(), 
                    "price" => self::GetPrice($props),
                    "price_offer" => self::GetOfferPrice($props),
                    "locathion" => self::GetLoc($props),
                    "date" => self::GetDate($props),
                    "text" => self::getText($props),
                    "imgs" => self::getImg($props),
                ));
            }
            return $carArr;
        } catch (Error $e) {
            ErrorApp::Err();
        }
    }

    private static function GetOfferPriceIndex($props) {
        if($props->has('.iva-item-badgeBarStep-rGgCo')) {
            $offr = $props->find('.iva-item-badgeBarStep-rGgCo')[0]->text();
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


    private static function GetLoc($props) {
        if($props->has('.geo-georeferences-Yd_m5')) {
            return $props->find('.geo-georeferences-Yd_m5')[0]->text();
        }
    }

    private static function GetOfferPrice($props) {
        if($props->has('.iva-item-badgeBarStep-rGgCo')) {
            return $props->find('.iva-item-badgeBarStep-rGgCo')[0]->text();
        }
    }

    private static function GetDate($props) {
        if($props->has('div[data-marker="item-date"]')) {
            return $props->find('div[data-marker="item-date"]')[0]->text();
        }
    }

    private static function GetPrice($props) {
        if($props->has('span[data-marker="item-price"]')) { 
            return $props->find('span[data-marker="item-price"]')[0]->text();
        } else if ($props->has('.ListingItemPrice-module__content')) {
            return $props->find('.ListingItemPrice-module__content span')[0]->text();
        }
    }

    private static function getText($props) {
        if($props->has('div[data-marker="item-specific-params"]')) {
            return $props->find('div[data-marker="item-specific-params"]')[0]->text();
        }
    }


    private static function getImg($props) {
        $imgArr = [];
        if($props->has('img')) {
            $imgs = $props->find('img[itemprop="image"]');
            foreach($imgs as $propImg) {
                array_push($imgArr, $propImg->getAttribute('src'));
            }
        }
            return $imgArr;
    }
}