<?php

namespace App\Controllers;

use App\Models\OrdersModel;
use  Exception;
use App\Libraries\RouterosAPI;

class PaymentController extends BaseController
{

    public function index()
    {
        return  view('creater-order-payment');
    }

    protected $helpers = ['form'];

    public function createOrder()
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

        // we create the database instance
        $orderM = new OrdersModel();

        // we obtain the post variables
        $post = $this->request->getPost(['mac', 'ip', 'email', 'plan']);

        // Agrega a la url el servicio a consumir

        $url = env('url_apiflow');
        $url = $url . '/payment/create';

        $secretKey = env('secretKey');
        $apikey = env('apikey');


        $textPlan = '';
        if ($post['plan'] === '1000') {
            $textPlan = '1 hora';
        } elseif ($post['plan'] === '3000') {
            $textPlan = '1 día';
        } elseif ($post['plan'] === '5000') {
            $textPlan = '2 días';
        } elseif ($post['plan'] === '10000') {
            $textPlan = '7 días';
        }

        $subject = 'Conexión a internet tiempo: ' . $textPlan;
        //optional
        $currency = 'CLP';
        $amount = $post['plan'];
        //$email
        $email = $post['email'];

        $optional = json_encode([
            'ip' => $post['ip'],
            'mac' => $post['mac']
        ]);

        $urlConfirmation = env('urlConfirmation');
        $urlReturn = env('urlReturn');
        // $s = 'la firma de los parámetros efectuada con su secretKey';

        // data to create order
        $data = [
            'amount' => $amount,
            'mac' => $post['mac'],
            'ip' => $post['ip'],
            'email' => $post['email'],
        ];


        // we should get it from database
        $idOrder = $orderM->insert($data);
        $lastOrder = $orderM->find($idOrder);

        //this is the commerceOrder to use in api
        $commerceOrder = $lastOrder['codeOrder'];

        $params = array(
            "apiKey" => $apikey,
            "commerceOrder" => $commerceOrder,
            "subject" => $subject,
            "currency" => $currency,
            "amount" => $amount,
            "email" => $email,
            "urlConfirmation" => $urlConfirmation,
            "urlReturn" => $urlReturn,
            "optional" => $optional
        );

        //we order keys
        $keys = array_keys($params);
        sort($keys);

        //concatenation
        $toSign = "";
        foreach ($keys as $key) {
            $toSign .= $key . $params[$key];
        };

        //signing
        $signature = hash_hmac('sha256', $toSign, $secretKey);


        //Consumiendo api mediante post 

        // echo json_encode($params);exit;
        //Add the signature to paramas
        $params["s"] = $signature;

        // echo json_encode($params);
        // exit;

        try {
            // Inicializa cURL
            $ch = curl_init();
            if ($ch === false) {
                throw new Exception('Failed to initialize cURL');
            }

            // Configura las opciones de cURL
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

            // Ejecuta la solicitud
            $response = curl_exec($ch);
            if ($response === false) {
                $error = curl_error($ch);
                throw new Exception('cURL error: ' . $error, curl_errno($ch));
            }

            // Obtiene la información de la respuesta
            $info = curl_getinfo($ch);

            // Verifica el código de estado HTTP
            if (!in_array($info['http_code'], array('200', '400', '401'))) {
                throw new Exception('Unexpected HTTP code: ' . $info['http_code'], $info['http_code']);
            }

            // Cierra la conexión cURL
            curl_close($ch);

            // Decodifica el JSON recibido
            $data = json_decode($response, true);
            if ($data === null) {
                throw new \Exception('Error decoding JSON response');
            }

            // Construye la URL final
            $finalUrl = $data['url'] . "?token=" . urlencode($data['token']);

            // Redirige al usuario usando CodeIgniter 4
            return redirect()->to($finalUrl);
        } catch (Exception $e) {
            // Manejo de errores
            echo 'Error: ' . $e->getCode() . ' - ' . $e->getMessage();
        }
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

                switch ($json_response['amount']) {
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
                        'mac-address' => $json_response['optional']['mac'],
                        'ip'     => $json_response['optional']['ip'], // Dirección IP del cliente
                        // 'server'      => 'hotspot1', // Nombre del servidor Hotspot
                    ]);
                }

                if (isset($mkconnec['!trap'])) {
                    echo 'Error: ' . $mkconnec['!trap'][0]['message'];
                } else {
                    // echo 'exito';
                    return  redirect()->to('https://www.google.com');
                }

                $API->disconnect(); // Desconectar de la API
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



    public function confirmation()
    {
        $confirmado = "Confirmado";
        return  $confirmado;
    }
}
