<?php 

//PARA SABER A QUE BASE DE DATOS SE ESTÁ APUNTANDO DESDE PHP

// Obtener el nombre de la base de datos
//$databaseName = \DB::connection()->getDatabaseName();
    
// Obtener la dirección IP del servidor de la base de datos
//$databaseHost = \DB::connection()->getConfig('host');

//return "Nombre de la base de datos: $databaseName <br> Dirección IP del servidor de la base de datos: $databaseHost";

//****************************************************** */

// Utilizar el algoritmo de encriptación bcrypt
// $hash = password_hash("angel123", PASSWORD_BCRYPT);

// var_dump($hash);


/**********************************************************/
/* AJUSTAR SECUENCIAS EN LA BASE DE DATOS */
//SELECT setval(pg_get_serial_sequence('service_providers', 'id'), MAX(id)) FROM service_providers;


$timestamp = time(); 

// Clave privada de la empresa (apikey)
$apikey = 'a2d6b8e143f29018c3d7e5a4b9c4f1e7'; // Aquí va tu clave privada
$privatekey = '7kfj5s3mb9qr263hxv2t98lwcn195ep4z6uy3b2v'; // Aquí va tu clave privada

// Concatenar el timestamp con el apikey para generar el hash
//$dataToHash = $timestamp . $apikey;

// Generar el hash MD5
//$md5Hash = md5($dataToHash);

$hashString = $timestamp . $apikey . $privateKey;
$md5Hash = md5($hashString);

// Imprimir los valores
echo "Timestamp: " . $timestamp . "\n";
echo "Public Key: " . $privatekey . "\n";
echo "MD5 Hash: " . $md5Hash . "\n";
?>


