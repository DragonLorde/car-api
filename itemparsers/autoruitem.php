<?php
require("devlPrice.php");


use DiDom\Document;


class Autoruitem {

    static public function GetAutoRuItem($url) {
        
        $data = CarItem::GetData($url);
        $auto = new Document ($data,  false);

        $price = self::GetPrice($auto);
        $offerPrices = self::GetOfferPrice($auto); 
        $own = self::GetOwn($auto);
        $milg = self::GetMilage($auto);

        $urlText = parse_url($url)['path'];
        $urlArr = explode('/' , $urlText);
        $v = self::GetDataTexts($auto);
        $itemArr = array(
            "url" => $url,
            // "items" => array(
            //     "mark" => $urlArr[4],
            //     "model" => $urlArr[5],
            //     "city" => self::GetCity($auto),
            //     "city_eng" => null,
            //     "min_year" => self::GetDateCar($auto),
            //     "max_year" => self::GetDateCar($auto),
            //     "trans" => self::GetTrans($auto),
            //     "milage" => self::GetMilage($auto),
            //     "wheel" => self::GetWheel($auto),
            //     "privod" => self::GetPrivod($auto),
            //     "body" => self::GetBody($auto),
            //     "min_v" => $v,
            //     "max_v" => $v,
            //     "viwes" => self::GetViews($auto),
            //     "date" => self::GetDate($auto),
            //     "owners" => self::GetOwn($auto),
            //     "vin" => self::GetVin($auto),
            // ),
            "item_text" => array(
                "price" => $price,
                "OfferPrice" => $offerPrices,
                "price_procent" => self::PrecntCalc(str_replace(" ", '', $milg) , $own, $price , $offerPrices),
                "mark" => $urlArr[4],
                "model" => $urlArr[5],
                "devl" => self::Devl(self::PrecntCalc(str_replace(" ", '', $milg) , $own, $price , $offerPrices) ,  $price),
                "city" => self::GetCity($auto),
                "city_eng" => null,
                "year" => self::GetDateCar($auto),
                "trans" => self::GetTransText($auto),
                "milage" => self::GetMilage($auto),
                "wheel" => self::GetWheelText($auto),
                "privod" => self::GetPrivodText($auto),
                "body" => self::GetBodyText($auto),
                "v" => $v,
                "viwes" => self::GetViews($auto),
                "date" => self::GetDate($auto),
                "owners" => self::GetOwn($auto),
                "vin" => self::GetVin($auto),
                "title" => self::GetTitle($auto),
            ),
            "text" => self::GetText($auto),
            "imgs" => self::GetImgs($auto),
            "price_analytic" => array(
                
            ),
        );
        
        echo json_encode($itemArr , JSON_UNESCAPED_UNICODE);
    }

    static function GetTitle($auto) {
        if($auto->has('.CardHead__title')) {
            $city = $auto->find('.CardHead__title');
            return $city[0]->text();
        }
    }

    static function Devl($pc , $p) {
        if($pc > $p) {
            $res = $pc - $p;
            $text =  "-" . $res;
            return $text;
        } else {
                        $res = $p - $pc;
            $text =  "+" . $res;
            return $text;
        }
    }

    static function PrecntCalc($milage, $own, $number , $offer) {
        if($offer) {
            $prcM = 0;
            $prcO = 0;
            $symb = false;
            if((int)$milage <= 120000 && (int)$own <= 3) {
                $symb = false;
                $prcM = 1;
                $prcO = 1;
            } else {
                $symb = true;
                $prcM = 2;
                $prcO = 2;
            }
            
            $percent = $prcO + $prcM; // Необходимый процент
            $number_percent = $number / 100 * $percent;
            if(!$symb) {
                $finalSum = $number + $number_percent; 
            } else {
                $finalSum = $number - $number_percent; 
            }
            return $finalSum;
        }
        return false;
    }

    static function GetPrice($drom) {
        if($drom->has('.OfferPriceCaption__price')) {
            $offer = $drom->find('.OfferPriceCaption__price');
            $RD = preg_replace('/[^0-9]/', '', $offer);
            return $RD[0];
        }
    }

    static function GetOfferPrice($drom) {
        if($drom->has('.OfferPriceBadge ')) {
            $offer = $drom->find('.OfferPriceBadge ')[0]->text();
            if($offer == 'без оценки') {
                return false;
            }
            return $offer;
        }
    }

    static function GetCity($auto) {
        if($auto->has('.MetroListPlace__regionName')) {
            $city = $auto->find('.MetroListPlace__regionName')[1]->text();
            return $city;
        }
    }

    static function GetDateCar($auto) {
        if($auto->has('.CardInfoRow_year')) {
            $cityDiv = $auto->find('.CardInfoRow_year a');
            $text = $cityDiv[0]->text();
            return $text;
        }
    }

    static function GetTrans($auto) {
        if($auto->has('.CardInfoRow_transmission')) {

             $trans = mb_strtolower($auto->find('.CardInfoRow_transmission')[0]->lastChild()->text());

            if($trans == 'механическая') {
                return 1;
            } else {
                return 0;
            }
        }
    }

    static function GetTransText($auto) {
        if($auto->has('.CardInfoRow_transmission')) {

             $trans = $auto->find('.CardInfoRow_transmission')[0]->lastChild()->text();

            return $trans;
        }
    }

    static function GetMilage($auto) {
        if($auto->has('.CardInfoRow_kmAge')) {
            $milgDiv = $auto->find('.CardInfoRow_kmAge');
            $text = $milgDiv[0]->lastChild()->text();
            $converText = mb_eregi_replace("[^0-9 ]", '', $text);
            return $converText;
        }
    }

    static function GetWheel($auto) {
        if($auto->has('.CardInfoRow_wheel')) {
            $wheel = $auto->find('.CardInfoRow_wheel');
            $text = mb_strtolower($wheel[0]->lastChild()->text());
            //var_dump($wheel);
            switch ($text) {
                case 'левый':
                    return 1;
                    break;
                case 'правый':
                    return 2;
                break;
                default:
                    return null;
                break;
            }
        }
    }

    static function GetWheelText($auto) {
        if($auto->has('.CardInfoRow_wheel')) {
            $wheel = $auto->find('.CardInfoRow_wheel');
            $text = $wheel[0]->lastChild()->text();
            return $text;
        }
    }

    static function GetPrivod($auto) {
        if($auto->has('.CardInfoRow_drive')) {
            $cityDiv = $auto->find('.CardInfoRow_drive');
            $text = mb_strtolower($cityDiv[0]->lastChild()->text());
            switch ($text) {
                case 'передний':
                    return 1;
                break;

                case 'задний':
                    return 2;
                break;

                case 'полный':
                    return 0;
                break;

                default:
                    return null;
                break;
            }
        }
    }

    static function GetPrivodText($auto) {
        if($auto->has('.CardInfoRow_drive')) {
            $cityDiv = $auto->find('.CardInfoRow_drive');
            $text = $cityDiv[0]->lastChild()->text();
            return $text;
        }
    }

    static function GetBody($auto) {
        if($auto->has('.CardInfoRow_bodytype')) {
            $trans = mb_strtolower($auto->find('.CardInfoRow_bodytype')[0]->lastChild()->text());

            switch ($trans) {
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
    }

    static function GetBodyText($auto) {
        if($auto->has('.CardInfoRow_bodytype')) {
            $trans = $auto->find('.CardInfoRow_bodytype')[0]->lastChild()->text();
            return $trans;
        }
    }

    static function GetDataTexts($auto) {
        if($auto->has('.CardInfoRow_engine')) {
            $milgDiv = $auto->find('.CardInfoRow_engine');
            $text = $milgDiv[0]->lastChild()->text();
            $textExp = explode("/" , $text);
            $vArr = explode(',' , mb_eregi_replace("[^0-9]", ',', $textExp[0]));
            $v = $vArr[0] . '.' . $vArr[1];
            return $v;
        }
    }

    static function GetViews($auto) {
        if($auto->has('.CardHead__views')) {
            $text = $auto->find('.CardHead__views')[0]->text();
            return $text;
        }
    }

    static function GetDate($auto) {
        if($auto->has('.CardHead__creationDate')) {
            $text = $auto->find('.CardHead__creationDate')[0]->text();
            return $text;
        }
    }

    static function GetText($auto) {
        if($auto->has('.CardDescription__text')) {
            $text = $auto->find('.CardDescription__text ')[0]->text();
            return $text;
        }
    }

    static function GetImgs($auto) {
        if($auto->has('.ImageGalleryDesktop')) {
            $imgBlock = $auto->find('.ImageGalleryDesktop__itemContainer img');
            $imgArr = [];
            foreach($imgBlock as $key => $prop) {
                array_push($imgArr , 'https:' . $prop->getAttribute('src'));
            }
            return $imgArr;
        }
            return null;
    }

    static function GetOwn($auto) {
        if($auto->has('.CardInfoRow_ownersCount')) {
            $text = $auto->find('.CardInfoRow_ownersCount')[0]->lastChild()->text();
            return mb_eregi_replace("[^0-9]", '', $text);
        }
    }

    static function GetVin($auto) {
        if($auto->has('.CardInfoRow_vin ')) {
            $vin = $auto->find('.CardInfoRow_vin')[0]->lastChild()->text();
            return $vin;
        }
        
    }

}