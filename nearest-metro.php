<?php
/*
 * Plugin Name: Шорткод ближайшей станции метро
 * Description: Ищет ближайшее метро около адреса, адрес задаётся через поле "Location" на странице
 * Version: 0.0.1
 * Author: Александр Боровлев
 * Author URI: https://vk.com/borovlioff
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

function is_empty(&$var)
{
    return !($var || (is_scalar($var) && strlen($var)));
}

function nearest_metro(){
    $apartment_coordinates = get_post_meta(get_the_ID(),'apartment_coordinates',true);
    $metro_name = get_post_meta(get_the_ID(),'metro_name',true);
    $metro_color = get_post_meta(get_the_ID(),'metro_color',true);
    $cord = "";

    $address = get_post_meta(get_the_ID(),'location',true);

        $colorRouteMetro = [
          ["name"=>"Сокольническая","color"=>"#EF161E"],
          ["name"=>"Замоскворецкая","color"=>"#2DBE2C"],
          ["name"=>"Арбатско-Покровская","color"=>"#0078BE"],
          ["name"=>"Филёвская","color"=>"#00BFFF"],
          ["name"=>"Кольцевая","color"=>"#8D5B2D"],
          ["name"=>"Калужско-Рижская","color"=>"#ED9121"],
          ["name"=>"Таганско-Краснопресненская","color"=>"#800080"],
          ["name"=>"Калининская","color"=>"#FFD702"],
          ["name"=>"Солнцевская","color"=>"#FFD702"],
          ["name"=>"Серпуховско-Тимирязевская","color"=>"#999999"],
          ["name"=>"Люблинско-Дмитровская","color"=>"#99CC00"],
          ["name"=>"Большаякольцевая","color"=>"#82C0C0"],
          ["name"=>"Каховская","color"=>"#82C0C0"],
          ["name"=>"Бутовская","color"=>"#A1B3D4"],
          ["name"=>"Монорельс","color"=>"#9999FF"],
          ["name"=>"Московскоецентральноекольцо","color"=>"#FFFFFF"],
          ["name"=>"Некрасовская","color"=>"#DE64A1"],
          ["name"=>"Коммунарская","color"=>"#D8D8D8"],
          ["name"=>"МЦД-1","color"=>"#f6a600"],
          ["name"=>"МЦД-2","color"=>"#e74280"],
          ["name"=>"МЦД-3","color"=>"#e95b0c"],
          ["name"=>"МЦД-4","color"=>"#40b280"],
          ["name"=>"МЦД-5","color"=>"#77b729"]
        ];
        if ( $apartment_coordinates !== '') {
          $metro = [
            'name' => $metro_name,
            'color' => $metro_color
        ];
      }
      else {
		if(!is_empty($address)){
        $parameters = array(
          'apikey' => 'key-your-yandex-api',
          'geocode' => $address, 
          'format' => 'json',
          'results' => 1
        );
    
        $response = file_get_contents('https://geocode-maps.yandex.ru/1.x/?'. http_build_query($parameters));
        $obj = json_decode($response, true);
    
        $cord = str_replace(" ", ",", $obj['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos']);
        update_post_meta( get_the_ID(), 'apartment_coordinates', $cord );

        $parameters = array(
          'apikey' => 'key-your-yandex-api',
          'geocode' => $cord,
          'kind' => 'metro',
          'format' => 'json',
          'results' => 1
        );
    
        $response = file_get_contents('https://geocode-maps.yandex.ru/1.x/?'. http_build_query($parameters));
        $obj = json_decode($response, true);
    
        $routeMetro = $obj['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['description'];
        $color = "#231F20";
        for($i = 0, $size = count($colorRouteMetro); $i < $size; ++$i){
    
          if(strpos($routeMetro , $colorRouteMetro[$i]['name']) !== false){
            $color = $colorRouteMetro[$i]['color'];
          }
      
        }
    
        $metro = [
                    'name' =>$obj['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['name'],
                    'color' => $color
                ];
        
        update_post_meta( get_the_ID(), 'metro_name', $obj['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['name'] );
        update_post_meta( get_the_ID(), 'metro_color', $color );
		}//if $address
		}//else

    $metro['name'] = str_replace('метро','м.', $metro['name']);
     return '<div class="location">
       <span class="metro-marker" style="background-color:'. $metro['color'] .'"></span>'
       .'<span class="metro-text">'. $metro['name'] .'</span>'.
        '</div>';

}
add_shortcode('metro', 'nearest_metro');
