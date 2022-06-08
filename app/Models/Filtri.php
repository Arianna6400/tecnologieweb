<?php

namespace App\Models;
use App\Models\Resource\Alloggio;
use App\Models\Resource\Caratteristiche;

Class Filtri{

public function filtroDimensione($max, $min){
    return Alloggio::where('Metratura', '>' , $min)->where('Metratura', '<' , $max)->get();
}

public function filtroPrezzo($max, $min){
    return Alloggio::where('Costo', '>' , $min)->where('Costo', '<' , $max)->get();
}

public function fitroPostiTotali($numeroPosti){
// ritorna un array di id di alloggi che anno quelle caratteristiche
    $id = Caratteristiche::where('PostiLettoTot', '>', $min)->where('PostiLettoTot', '<', $max)->select('ID')->get();
    return Alloggio::find($id);
}

public function filtroserviziAggiuntivi($nomeServizio){
switch($nomeServizio){

case 'ripostiglio': {
$id = Caratteristiche::where('Ripostiglio', '>', 0)->select('ID')->get();
return Alloggio::find($id);
}
case 'sala':{
$id = Caratteristiche::where('Sala', '>', 0)->select('ID')->get();
return Alloggio::find($id);
}
case 'wifi':{
$id = Caratteristiche::where('WiFi', '>', 0)->select('ID')->get();
return Alloggio::find($id);
}
case 'garage':{
$id = Caratteristiche::where('Garage', '>', 0)->select('ID')->get();
return Alloggio::find($id);
}
case 'angolo_studio':{
$id = Caratteristiche::where('AngoloStudio', '>', 0)->select('ID')->get();
return Alloggio::find($id);
}
}
}

public function filtroNumeroLocali($scelta){
switch($scelta){
case '2': {
$id = Caratteristiche::where('NumeroLocali', 2)->select('ID')->get();
return Alloggio::find($id);
}
case '3': {
$id = Caratteristiche::where('NumeroLocali', 3)->select('ID')->get();
return Alloggio::find($id);
}
case '+3': {
$id = Caratteristiche::where('NumeroLocali', '>', 3)->select('ID')->get();
return Alloggio::find($id);
}
}
}

//aggiungere nel database l'attributo posti letto stanza
public function filtropostiLettoStanza($scelta){
$id = Caratteristiche::where('NumPostiStanza', 1)->select('ID')->get();
return Alloggio::find($id);

}

public function filtroNumeroBagni($scelta){
$id = Caratteristiche::where('NumBagni', 2)->select('ID')->get();
return Alloggio::find($id);
}

public function filtronumeroStanze($scelta) {
switch($scelta){
case '2': {
$id = Caratteristiche::where('NumStanzeLetto', 2)->select('ID')->get();
return Alloggio::find($id);
}
case '3': {
$id = Caratteristiche::where('NumStanzeLetto', 3)->select('ID')->get();
return Alloggio::find($id);
}
case '+3': {
$id = Caratteristiche::where('NumStanzeLetto', '>', 3)->select('ID')->get();
return Alloggio::find($id);
}
}
}

public function filtroEtaMinima($scelta){
switch($scelta){
case '18': {
$id = Caratteristiche::where('EtaMinima', '>' , 18)->select('ID')->get();
return Alloggio::find($id);
}
case '25': {
$id = Caratteristiche::where('EtaMinima', '>' , 25)->select('ID')->get();
return Alloggio::find($id);
}
case '30': {
$id = Caratteristiche::where('EtaMinima', '>', 30)->select('ID')->get();
return Alloggio::find($id);
}
}
}

public function filtroSessoRischiesto($scelta){
if($scelta == 'M'){
$id = Caratteristiche::where('SessoRichiesto', 'M')->select('ID')->get();
return Alloggio::find($id);
}else{
$id = Caratteristiche::where('SessoRichiesto', 'F')->select('ID')->get();
return Alloggio::find($id);
}
}

}