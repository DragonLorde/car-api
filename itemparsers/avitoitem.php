<?php
use DiDom\Document;

class Avitoitem {

    static public function GetAvitoItems($url) {
        
        $data = CarItem::GetData($url);
        $auto = new Document ($data,  false);

        $price = self::GetPrice($auto);
        $offerPrices = self::GetOfferPrice($auto);
        $procent = self::PrecntCalc($offerPrices , $auto , $price); 

        $urlText = parse_url($url)['path'];
        $urlArr = explode('/' , $urlText);
        $urlData = explode('_' , $urlArr[3]);
        //$v = self::GetDataTexts($auto);
        $itemArr = array(
            "url" => $url,
            // "items" => array(
            //     "mark" => $urlData[0],
            //     "model" => $urlData[1],
            //     "city" => self::GetCity($auto),
            //     "city_eng" => $urlArr[1],
            //     "min_year" => $urlData[2],
            //     "max_year" => $urlData[2],
            //     "trans" => self::GetTrans($auto),
            //     "milage" => self::GetMilage($auto ),
            //     "wheel" => self::GetWheel($auto),
            //     "privod" => self::GetPrivod($auto),
            //     "body" => self::GetBody($auto),
            //     "min_v" => self::GetV($auto),
            //     "max_v" => self::GetV($auto),
            //     "viwes" => self::GetViews($auto),
            //     "date" => self::GetDate($auto),
            //     "owners" => self::GetOwn($auto),
            //     "vin" => self::GetVin($auto),
            // ),
            "item_text" => array(
                "price" => $price,
                "OfferPrice" => $offerPrices,
                "price_procent" => $procent,
                "mark" => ucfirst($urlData[0]),
                "model" => ucfirst($urlData[1]),
                "city" => self::GetCity($auto),
                "city_eng" => $urlArr[1],
                "min_year" => $urlData[2],
                "trans" => self::GetItem($auto, 'Коробкапередач'),
                "milage" => preg_replace('/[^0-9]/', '', self::GetItem($auto, 'Пробег')),
                "wheel" => self::GetItem($auto, 'Руль'),
                "privod" => self::GetItem($auto, 'Привод'),
                "body" => self::GetItem($auto, 'Типкузова'),
                "v" => self::GetV($auto),
                "viwes" => self::GetViews($auto),
                "date" => self::GetDate($auto),
                "owners" => self::GetOwn($auto),
                "vin" => self::GetVin($auto),
            ),
            "text" => self::GetText($auto),
            "imgs" => self::GetImgs($auto),
            "price_analytic" => array(
                
            ),
        );
        
        echo json_encode($itemArr , JSON_UNESCAPED_UNICODE);
       
    }

    static function PrecntCalc( $offer, $auto, $price ) {
        if($offer) {
            if($auto->has('.styles-subtitle-container-v7qnO')) {
                $price_rec = preg_replace('/[^0-9]/', '',  $auto->find('.styles-subtitle-container-v7qnO')[0]->text());
                if($price_rec) {
                    return $price - $price_rec;
                } else {
                    $number_percent = $price / 100 * 1.1;

                    return $price - $number_percent;
                }
            }
        }
        return false;
    }

    static function GetPrice($drom) {
        if($drom->has('.js-item-price')) {
            $offer = $drom->find('.js-item-price')[0]->text();
            $RD = preg_replace('/[^0-9]/', '', $offer);
            return $RD;
        }
    }

    static function GetOfferPrice($drom) {
        if($drom->has('.styles-chart-2sQbJ h4')) {
            $offer = explode("—" ,$drom->find('.styles-chart-2sQbJ h4')[0]->text())[1];
            if($offer == 'без оценки') {
                return false;
            }
            return $offer;
        } else {
            return false;
        }
    }

    static function GetText($auto) {
        if($auto->has('.item-description-text p')) {
            $text = $auto->find('.item-description-text p')[0]->text();
            return $text;
        }
    }

    static function GetImgs($auto) {
        if($auto->has('.gallery-list')) {
            $imgBlock = $auto->find('.gallery-img-frame');
            $imgArr = [];
            foreach($imgBlock as $key => $prop) {
                array_push($imgArr , $prop->getAttribute('data-url'));
            }
            return $imgArr;
        }
            return null;
    }

    static function GetItem($auto , $word) {
        if($auto->has('.item-params-list')) {
            $items = $auto->find('.item-params-list-item');
            foreach($items as $prop => $key) {
                if(mb_strtolower ( explode(':' , str_replace(' ' , '', $key->text()))[0] )  == mb_strtolower ($word)) {
                    $text = explode(':' , str_replace(' ' , '', $key->text()))[1];
                    return mb_strtolower($text);
                }
            }
        }
    }

    static function GetMilage($auto) {
        $text = mb_eregi_replace("[^0-9 ]", '', self::GetItem($auto , 'пробег'));
        return $text;
    }

    static function GetTrans($auto) {
        $trans = self::GetItem($auto , 'Коробкапередач');
        if($trans == 'автомат' || $trans == 'робот') {
            return 1;
        } else {
            return 0;
        }
    }

    static function GetWheel($auto) {
        $trans = self::GetItem($auto , 'Руль');
        if($trans == 'левый') {
            return 1;
        } else {
            return 2;
        }
    }

    static function GetBody($auto) {
            $trans = self::GetItem($auto , 'Типкузова');
            
            switch ($trans) {
                case 'хэтчбек 5 дв':
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
    
    static function GetV($auto) {
        $text = self::GetItemV($auto , 'Модификация');
        return explode(" " , $text)[1];
    }

    static function GetItemV($auto , $word) {
        if($auto->has('.item-params-list')) {
            $items = $auto->find('.item-params-list-item');
            foreach($items as $prop => $key) {
                if(mb_strtolower ( explode(':' , str_replace(' ' , '', $key->text()))[0] )  == mb_strtolower ($word)) {
                    $text = explode(':' ,  $key->text())[1];
                    return mb_strtolower($text);
                }
            }
        }
    }

    static function GetViews($auto) {
        if($auto->has('.title-info-metadata-item')) {
            $text = $auto->find('.title-info-metadata-item')[0]->text();
            return $text;
        }
    }

    static function GetDate($auto) {
        if($auto->has('.title-info-metadata-item-redesign')) {
            $text = $auto->find('.title-info-metadata-item-redesign')[0]->text();
            return $text;
        }
    }

    static function GetCity($auto) {
        if($auto->has('.item-address__string')) {
            $text = $auto->find('.item-address__string')[0]->text();
            return $text;
        }
    }

    static function GetOwn($auto) {
        $text = self::GetItemV($auto , 'ВладельцевпоПТС');
        return $text;
    }

    static function GetVin($auto) {
        $text = self::GetItemV($auto , 'VINилиномеркузова');
        return mb_strtoupper($text);
    }

    static function GetPrivod($auto) {
            $trans = self::GetItem($auto , 'Привод');

            switch ($trans) {
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