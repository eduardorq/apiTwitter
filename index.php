<?php
require_once('./includes/TwitterAPIExchange.php');

$tiempo_inicio = microtime(true);

// Datos para conectar con la App de Twitter GCF-Sentinel
$settings = array(
    'oauth_access_token' => "",
    'oauth_access_token_secret' => "",
    'consumer_key' => "",
    'consumer_secret' => ""
);


/*
// Obtener timeline de un usuario
$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
$getfield = '?screen_name=eduardorq&count=10';
$requestMethod = 'GET';
$twitter = new TwitterAPIExchange($settings);
$json =  $twitter->setGetfield($getfield)
                     ->buildOauth($url, $requestMethod)
                     ->performRequest();
*/

/*
// Obtener los seguidores
$url = 'https://api.twitter.com/1.1/followers/list.json';
$getfield = '?screen_name=eduardorq';
$requestMethod = 'GET';
$twitter = new TwitterAPIExchange($settings);
$json =  $twitter->setGetfield($getfield)
                     ->buildOauth($url, $requestMethod)
                     ->performRequest();
*/

/*
// Obtener tweets
$url = 'https://api.twitter.com/1.1/search/tweets.json';
$getfield = '?q=#gratis&geocode=27.989149,-15.583952,40km&lang=es&result_type=recent';
$requestMethod = 'GET';
$twitter = new TwitterAPIExchange($settings);
$json =  $twitter->setGetfield($getfield)
                     ->buildOauth($url, $requestMethod)
                     ->performRequest();

// para desgranar los hashtags
$res = (array) json_decode($json);
//print_r($res);
if(!empty($res['statuses'])){
    //echo "<b>Statuses:</b> ".$res['statuses']."<br><br>";
    //print_r($res['statuses'][4]->entities->hashtags[0]->text);

    // Extrae los hashtags del tuit
    $entities = (array) $res['statuses'][4]->entities->hashtags;
    $hashtags = "<b>Hashtags: </b>";

    foreach ($entities as $hashtag) {
        $hashtags .= $hashtag->text.", ";
    }
    echo $hashtags;

    echo "<br><b>Search Metadata:</b> <br>
    query: ".$res['search_metadata']->query."<br>
    refresh_url: ".$res['search_metadata']->refresh_url
    ;
}
*/

/*

 // USANDO LA PREMIUM SEARCH
$url = 'https://api.twitter.com/1.1/tweets/search/30day/Sandbox.json';
$getfield = '?q=gratis';
$requestMethod = 'GET';
$twitter = new TwitterAPIExchange($settings);
$json =  $twitter->setGetfield($getfield)
                     ->buildOauth($url, $requestMethod)
                     ->performRequest();
*/


// Obtiene el timeline de los perfiles a los que sigues
$url = 'https://api.twitter.com/1.1/statuses/home_timeline.json';
$getfield = '?exclude_replies=true&include_entities=true&tweet_mode=extended&count=200';
$requestMethod = 'GET';
$twitter = new TwitterAPIExchange($settings);
$json =  $twitter->setGetfield($getfield)
                     ->buildOauth($url, $requestMethod)
                     ->performRequest();

// Imprime el JSON
//echo $json;

// Configuración de la búsqueda.
$conf = array(
            "checkHashtags" => "si",
            "checkText" => "si"
);

// Array de Hashtags que comprueba si coinciden dentro del texto de los tuits
$qHashtags = array("noticias, canarias");

// Array de Palabras que comprueba si coinciden dentro del texto de los tuits
$qPalabras = array("museos");

$res = (array) json_decode($json);
$entities = array();

// Si la consulta no viene vacía
if(!empty($res)){

    // Por cada tuit
	foreach ($res as $tuit) {

        // Si no es retweet
        if(!$tuit->retweeted_status){

            // Hashtags
            $hashtags = (array) $tuit->entities->hashtags;

            // Almaceno el ID del tuit
            $idTuit = $tuit->id_str;

            // Si en la configuración seleccionamos que revise los hashtags que le hayamos definido para buscar d eun array.
            if($conf['checkHashtags'] === "si"){

                // Almacena los hashtags con el id del tuit
                foreach ( $hashtags as $hashtag ){   

                    // Hago la comparación del hashtag con el array de posibilidades
                    foreach ( $qHashtags as $termino ){

                        // Si encuentra el hashtag en el tuit
                        if(stripos($hashtag->text, $termino)!==false){

                            
                            // Si existe el tuit, añade el hashtag al id de tuit existente
                            if (array_key_exists($idTuit, $entities)) {

                                array_push($entities[$idTuit]['hashtags'], $hashtag->text);

                            }else{
                            // Si no está registrado el tuit en el array, crea el índice con el id del tuit
                                $entities[$idTuit]['hashtags'] = array($hashtag->text);

                            }



                            // Media entities (IMÁGENES)
                            if($tuit->extended_entities->media){
                                $images = (array) $tuit->extended_entities->media;

                                // Almacena las imágenes asociadas al id del tuit
                                foreach ( $images as $image ){
                                    
                                    // Si existe el tuit, añade la imagen al id de tuit existente
                                    if (array_key_exists($idTuit, $images)) {

                                        array_push($entities[$idTuit]['images'], $image->media_url);

                                    }else{
                                    // Si no está registrado el tuit en el array, crea el índice con el id del tuit
                                        $entities[$idTuit]['images'] = array($image->media_url);

                                    }
                                }
                            }


                            // Media entities (ENLACES)
                            if($tuit->entities->urls){
                                $urls = (array) $tuit->entities->urls;

                                // Almacena las imágenes asociadas al id del tuit
                                foreach ( $urls as $url ){
                                    
                                    // Si existe el tuit, añade la imagen al id de tuit existente
                                    if (array_key_exists($idTuit, $urls)) {

                                        array_push($entities[$idTuit]['urlTuit'], $url->url);

                                    }else{
                                    // Si no está registrado el tuit en el array, crea el índice con el id del tuit
                                        $entities[$idTuit]['urlTuit'] = array($url->url);
                                    }
                                }
                            }

                            // @Autor del tuit
                            $entities[$idTuit]['user'] = $tuit->user->screen_name;

                            // Texto del tuit
                            $entities[$idTuit]['text'] = $tuit->full_text;

                        }
                    }
                }
            }


            // Si en la configuración le definimos que busque por palabras que le hayamos definido para buscar de un array.
            if($conf['checkText'] === "si"){
                
                $textoTuit = (array)$tuit->full_text;

                foreach ($textoTuit as $texto){
                    echo $texto."<br>";
                    // Hago la comparación de la palabra con el array de posibilidades
                    foreach ( $qPalabras as $palabra ){
                    
                        if(stripos($texto, $palabra)!==false){


                            if(!array_key_exists($idTuit, $entities)){

                                // Si el tuit ya no ha sido guardado previamente por hashtag, si está activa la búsqueda por hashtag, entonces guardar.

                                echo $entities[$idTuit]['text'];
                            }
                        }
                    }
                }
            }
        }
	}
	//var_dump($entities);
}

$tiempo_fin = microtime(true);
echo "<br><br>El script ha tardado en ejecutarse: ".number_format($tiempo_fin-$tiempo_inicio,3)." segundos";

// TODO:
/*
Búsqueda por hashtags (Hecho)
Búsqueda por palabras claves en el texto
*/

?>