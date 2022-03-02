<?php

function Test() {
    $start = microtime(true);

    $urls = array(
        'https://www.avito.ru/tyumen/avtomobili/volkswagen/polo',
      
    );
     
    $multi = curl_multi_init();
    $channels = array();
     
    foreach ($urls as $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 


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
     
    foreach ($channels as $channel) {
       // echo curl_multi_getcontent($channel);
        curl_multi_remove_handle($multi, $channel);
    }
    $last_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    print_r($last_url);
    curl_multi_close($multi);
    echo 'Время выполнения скрипта: '.round(microtime(true) - $start, 4).' сек.';

}

function Test2() {
    $start = microtime(true);

    $ch = curl_init('https://www.avito.ru/tyumen/avtomobili/volkswagen/polo/?p=10');
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
    http_response_code(308);
    $html = curl_exec($ch);
    $last_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    print_r($last_url);
    curl_close($ch);
    echo 'Время выполнения скрипта: '.round(microtime(true) - $start, 4).' сек.';

}