<?php

namespace App\Controllers;

use App\Models\OrdersModel;
use  Exception;
use App\Libraries\RouterosAPI;

class TestMikrotikController extends BaseController
{

    protected $helpers = ['form'];

    public function logmikrotik()
    {
        $rules = [
            'email' => 'required|max_length[100]|valid_email',
            'mac' => 'required|max_length[50]',
            'ip' => 'required|valid_ip[ipv4]',
        ];

        if (!$this->validate($rules)) {
            // return redirect()->back()->withInput()->with('errors', $this->validator->listErrors());
            return;
        }

        // we obtain the post variables
        $post = $this->request->getPost(['mac', 'ip', 'email', 'plan']);

        $ip = env('ip_mikrotik');
        $username = env('username_mikrotik');
        $password = env('password_mikrotik');
        $port = env('port_mikrotik');
        $API = new RouterosAPI();
        $API->debug = false;
        $API->port = $port;
        $userLog = '';
        $users = [
            'user_1000',
            'user_3000',
            'user_5000',
            'user_10000',
        ];
        switch ($post['plan']) {
            case '1000':
                # loguear al usuario por 1 hora
                $userLog = $users[0];
                break;

            case '3000':
                # loguear al usuario por 2 días
                $userLog = $users[1];
                break;

            case '5000':
                # loguear al usuario por 7 días
                $userLog = $users[2];
                break;
            case '10000':
                # loguear al usuario por 7 días
                $userLog = $users[3];
                break;
        }


        if ($API->connect($ip, $username, $password)) {
            $mkconnec = $API->comm('/ip/hotspot/active/login', [
                'user' => $userLog,
                'password' => 'M0v1n3t20',
                'mac-address' => $post['mac'],
                'ip'     => $post['ip'], // Dirección IP del cliente
                // 'server'      => 'hotspot1', // Nombre del servidor Hotspot
            ]);
        }

        if (isset($mkconnec['!trap'])) {
            echo 'Error: ' . $mkconnec['!trap'][0]['message'];
        }

        $API->disconnect(); // Desconectar de la API


    }

    public function getstatuspayment()
    {
        $url = env('url_apiflow');
        $url = $url . '/payment/getStatus';

        //obtaining post variable
        $post = $this->request->getPost();
        // echo print_r($post);

        $params = array(
            "apiKey" => env('apikey'),
            "token" => $post['token']
        );

        //order my keys
        $keys = array_keys($params);
        sort($keys);

        //concatenation 
        $toSign = "";
        foreach ($keys as $key) {
            $toSign .= $key . $params[$key];
        };

        $signature = hash_hmac('sha256', $toSign, env('secretKey'));

        // agrega la firma a los parámetros
        $params["s"] = $signature;

        $url = $url . "?" . http_build_query($params);
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $response = curl_exec($ch);
            if ($response === false) {
                $error = curl_error($ch);
                throw new Exception($error, 1);
            }
            $info = curl_getinfo($ch);
            if (!in_array($info['http_code'], array('200', '400', '401'))) {
                throw new Exception('Unexpected error occurred. HTTP_CODE: ' . $info['http_code'], $info['http_code']);
            }

            $json_response = json_decode($response, true);

            // echo json_encode($response);exit;

            //logic to connecte to mikrotik
            if ($json_response['status'] === 2) {

                $orderM = new OrdersModel();

                $orderM->where('codeOrder', $json_response['commerceOrder'])
                    ->set(['status' => 'PAGADA', 'email' => $json_response['payer']])
                    ->update();



                http_response_code(200);
            } elseif ($json_response['status'] === 3) {
                echo  '<h2>El pago ha sido rechazado</h2>';
            } elseif ($json_response['status'] === 4) {
                echo  '<h2>El pago ha sido anulado</h2>';
            } elseif ($json_response['status'] === 1) {
                echo  '<h2>El pago esta pendiente</h2>';
            }
            // echo $response;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getCode() . ' - ' . $e->getMessage();
        }
    }
}
