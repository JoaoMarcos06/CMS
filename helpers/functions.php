<?php

function formatCurrency(float $value){
    return number_format($value, 2, ",",".");
}

function removeTags($text){
    return strip_tags($text);
}

function formatDate($date){
    $dateTime = explode(" ", $date);
    return date(" j F, y h:i:s", strtoTime($date));
}

function Year(){
    return date('Y');
}

function writeHtml($html){
    echo $html;
}

?>