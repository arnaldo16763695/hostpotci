<?php
use App\Libraries\RouterosAPI;
include 'RouterosAPI.php';
$ip = "192.168.45.1";
$username = "arnaldo";
$password = "M0v1n3t20";
$port = "8728";


$API = new RouterosAPI();
$API->debug = false;
$API->port = $port;

// if ($API->connect($ip, $username, $password)) {

    // $response = $API->comm('/interface/print');

    // if (!empty($response)) {
    //     echo "Lista de interfaces:\n";
    //     foreach ($response as $interface) {
    //         echo "Nombre: " . $interface['name'] . "\n";
    //         echo "ID: " . $interface['.id'] . "\n";
    //         echo "Tipo: " . $interface['type'] . "\n";
    //         echo "Estado: " . ($interface['running'] == 'true' ? 'Activo' : 'Inactivo') . "\n";
    //         echo "------------------------\n";
    //     }
    // } else {
    //     echo "No se encontraron interfaces.\n";
    // }

    // $response = $API->comm('/ip/hotspot/active/login', [
    //     'mac-address' => '94:E2:3C:A6:CE:1C',
    //     'user' => 'T-94:E2:3C:A6:CE:1C',
    //     'ip'     => '192.168.45.247', // Dirección IP del cliente
    //     // 'server'      => 'hotspot1', // Nombre del servidor Hotspot
    // ]);

    // if (isset($response['!trap'])) {
    //     // echo 'Error: ' . $response['!trap'][0]['message'];
    //     echo 'fallita';
    // } else {
    //     echo 'exito';
    // }

    // $API->disconnect(); // Desconectar de la API
// } else {
//     echo 'falló conexion';
// }
