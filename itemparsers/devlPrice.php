<?php

function GetDevlCarP() {
    $array = array(
        "model"=>"Logan",
        "year"=>"2015",
        "probeg"=>"129000",
        "mark"=>"Renault",
        "body"=>"Седан",
        "equipment"=>"средний"
    );		
    
    $ch = curl_init('https://cena-auto.ru/calculator/ajax_send/');
    
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $array); 
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $html = curl_exec($ch);
    curl_close($ch);
     
    echo $html;
}