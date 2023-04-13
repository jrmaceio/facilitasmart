<?php

class GoogleMaps1 extends TWindow
{
private $pos;
function __construct()
{
parent::__construct();
parent::setTitle('Localização');
parent::setSize(630,450);
}

public function onSearch($param)
{
$address = $param['key'];
$address = str_replace(" ", "+", $address); // replace all the white space with "+" sign to match with google search pattern

$url = "http://maps.googleapis.com/maps/api/js?key=ColoqueASuaKeyAqui&amp;sensor=false";

$response = file_get_contents($url);

$json = json_decode($response,TRUE); //generate array object from the response from the web

//echo ($json['results'][0]['geometry']['location']['lat'].",".$json['results'][0]['geometry']['location']['lng']);
$lt = $json['results'][0]['geometry']['location']['lat'];
$lg = $json['results'][0]['geometry']['location']['lng'];

$this->pos = $lt.','.$lg;

$mapElement = new TElement('img');
$mapElement->generator = 'adianti';
$mapElement->src = "https://maps.googleapis.com/maps/api/staticmap?center=".$this->pos."&zoom=15&size=600x400&markers=color:red%7Clabel:C%7C".$this->pos."&key=sua Key Aqui";

parent::add($mapElement);
}
public function show()
{
$this->loaded = true;
parent::show();
}

}

?> 