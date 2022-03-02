<?php
use DiDom\Document;


class Dromitem {


    static public function GetDromItems($url) {
        
        $data = CarItem::GetData($url);
        $drom = new Document ($data,  false, 'windows-1251');
        $textBase = self::GetInfo($drom);
        
        $urlText = parse_url($url)['host'];
        $parseUrl = explode("." , $urlText);

        $markModel = explode('/', parse_url($url)['path']);
        $offerPrices = self::GetOfferPrice($drom); 
        $price = self::GetPrice($drom);
        $own = self::GetOwn($drom);
        $milg = self::GetItem($drom , 'Пробег, км');
        $prcnt =  self::PrecntCalc(str_replace(" ", '', $milg) , $own, $price , $offerPrices);
        $itemArr = array(
            "url" => $url,
            // "items" => array(
            //     "mark" => $markModel[1],
            //     "model" => $markModel[2],
            //     "city_ru" => self::GetCity($drom),
            //     "city" => $parseUrl[0],
            //     "min_year" => $textBase[3],
            //     "max_year" => (int)$textBase[3] + 1,
            //     "trans" => self::GetTrans($drom),
            //     "min_milage" => self::GetMilage($drom),
            //     "wheel" => self::GetWheel($drom),
            //     "privod" => self::GetPrivod($drom),
            //     "body" => self::GetBody($drom),
            //     "min_v" => self::GetV($drom),
            //     "max_v" => self::GetV($drom),
            //     "viwes" => self::GetViews($drom),
            //     "date" => self::GetDate($drom),
            //     "owners" => self::GetOwn($drom),
            //     "vin" => self::GetVin($drom),
            // ),
            'items_text' => array(
                "price" => $price,
                "price_procent" => $prcnt[0],
                "OfferPrice" => $offerPrices,
                "devl" => $prcnt[1],
                "mark" => ucfirst($markModel[1]),
                "model" => ucfirst($markModel[2]),
                "city_ru" => self::GetCity($drom),
                "city" => $parseUrl[0],
                "year" => $textBase[3],
                "trans" => self::GetItem($drom , 'Трансмиссия'),
                "min_milage" => $milg,
                "wheel" => self::GetItem($drom , 'Руль'),
                "privod" => self::GetItem($drom, 'Привод'),
                "body" => self::GetItem($drom ,'Тип кузова'),
                "v" => self::GetV($drom),
                "viwes" => self::GetViews($drom),
                "date" => self::GetDate($drom),
                "owners" => $own,
                "vin" => self::GetVin($drom),
            ),
            "text" => self::GetText($drom),
            "imgs" => self::GetImgs($drom),
            "price_analytic" => array(
                
            ),
        );
        // $price = new Prices($itemArr['items']);
        // $url = $price->GetUrls();
        // var_dump($url);
        echo json_encode($itemArr , JSON_UNESCAPED_UNICODE);
    }


    static function PrecntCalc($milage, $own, $number , $offer) {
        if($offer) {
            $prcM = 0;
            $prcO = 0;
            $symb = false;
            if((int)$milage <= 120000 && (int)$own <= 3) {
                $symb = false;
                $prcM = 1.4;
                $prcO = 1.4;
            } else {
                $symb = true;
                $prcM = 2;
                $prcO = 2;
            }
            $devl = 0;
            $percent = $prcO + $prcM; // Необходимый процент
            $number_percent = $number / 100 * $percent;
            if(!$symb) {
                $finalSum = $number + $number_percent;
                $devl =  $finalSum - $number; 
                $devlT = '-' . $devl;
            } else {
                $finalSum = $number - $number_percent;
                $devl = $number - $finalSum; 
                $devlT = '+' . $devl;
            }
            return [$finalSum, $devlT];
        }
        return false;
    }

    static function GetPrice($drom) {
        if($drom->has('.css-1003rx0')) {
            $offer = $drom->find('.css-1003rx0')[0]->text();
            return preg_replace('/[^0-9]/', '', $offer);
        }
    }

    static function GetOfferPrice($drom) {
        if($drom->has('.ejipaoe0')) {
            $offer = $drom->find('.ejipaoe0')[0]->text();
            if($offer == 'без оценки') {
                return false;
            }
            return $offer;
        }
    }

    static function GetImgs($drom) {
        if($drom->has('div[data-ftid="bull-page_bull-gallery_thumbnails"] a')) {
            $imgBlock = $drom->find('div[data-ftid="bull-page_bull-gallery_thumbnails"] a');
            $imgArr = [];
            foreach($imgBlock as $key => $prop) {
                array_push($imgArr , $prop->getAttribute('href'));
            }
            return $imgArr;
        }
            return null;
    }

    static function GetInfo($drom) {
        if($drom->has('.css-1rmdgdb')) {
            $text = $drom->find('.css-1rmdgdb')[0]->text();
            $textArr = explode(" " , str_replace( ',' , '' , $text));
            
            return $textArr;
        }
            return null;
    }

    static function GetV($drom) {
        if($drom->has('tbody')) {
            $vNode = $drom->find('tbody')[0]->find('td span ');
            $v = $vNode[0]->text();
            $vPreg = explode( ",",  preg_replace("~[^-a-z0-9_]+~", ',', $v) );
            return $vPreg[1] . '.' . $vPreg[2];
        }
    }

    static function GetCity($drom) {
        if($drom->has('.css-13smvac')) {
            $cnt = count($drom->find('.css-13smvac'));
            $city = null;
            if($cnt == 2) {
                $city = $drom->find('.css-13smvac')[1]->text();
            } else {
                $city = $drom->find('.css-13smvac')[2]->text();
            }            
            return explode(" ", $city)[1];
        }
    }

    static function GetText($drom) {
        if($drom->has('.css-13smvac')) {
            $cnt = count($drom->find('.css-13smvac'));
            $text = null;
            if($cnt == 2) {
                $text = $drom->find('.css-13smvac span')[1]->text();
            } else {
                $text = $drom->find('.css-13smvac span')[1]->text();
            }       
            return $text;
        }
    }

    static function GetViews($drom) {
        if($drom->has('.css-193s9zx')) {
            $viwes = $drom->find('.css-193s9zx')[0]->text();
            return $viwes;
        }
    }

    static function GetDate($drom) {
        if($drom->has('.css-pxeubi ')) {
            $date = $drom->find('.css-pxeubi ')[0]->text();
            return  $date;
        }
    }

    static function GetItem($auto , $word) {
        if($auto->has('.css-11ylakv')) {
            $items = $auto->find('.css-11ylakv th');
            foreach($items as $prop => $key) {
                if(mb_strtolower($key->text()) == mb_strtolower($word)) {
                    $text = $key->nextSibling();
                    return $text->text();
                }
            }
        }
    }

    static function GetTrans($auto) {
        $text = self::GetItem($auto ,'Трансмиссия');

        if($text == 'механика') {
            return 1;
        } else {
            return 0;
        }
    }

    static function GetMilage($auto) {
        $text = self::GetItem($auto ,'Пробег, км');

        return str_replace(" " , '' , $text);
    }

    static function GetWheel($auto) {
        $text = self::GetItem($auto ,'Руль');

        if($text == 'левый') {
            return 1;
        } else {
            return 2;
        }
    }

    static function GetPrivod($auto) {
        $text = self::GetItem($auto ,'Привод');

        switch ($text) {
            case 'передний':
                return 1;
                break;
            case 'задний':
                return 2;
                break;
            case '4WD':
                return 0;
            break;
        }
    }

    static function GetBody($auto) {
        $text = self::GetItem($auto ,'Тип кузова');

        switch ($text) {
            case 'хэтчбек 5 дв.':
                return 1;
                break;
            case 'седан':
                return 0;
                break;
            case 'лифтбек':
                return 4;
                break; 
            default: 
                return null;
                break;
            }
    }

    static function GetOwn($auto) {
        $text = self::GetItem($auto ,'Регистрации');

        return mb_eregi_replace("[^0-9]", '', $text);
    }

    static function GetVin($auto) {
        if($auto->has('.css-1hfjiu2')) {
            $vin = $auto->find('.css-1hfjiu2')[0]->text();
            return $vin;
        }
    }
}