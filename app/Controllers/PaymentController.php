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


        $codeOrder = 'HOTSPOT-' . date('Ymd-His') . '-' . random_int(1000, 9999);

        // data to create order
        $data = [
            'codeOrder' => $codeOrder,
            'amount' => $amount,
            'mac' => $post['mac'],
            'ip' => $post['ip'],
            'email' => $post['email'],
        ];


        // insert en BD
        $orderM = new OrdersModel();
        $idOrder = $orderM->insert($data);
        $lastOrder = $orderM->find($idOrder);

        // commerceOrder que se usa en Flow
        $commerceOrder = $lastOrder['codeOrder'];   // o directamente $codeOrder

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

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

            $response = curl_exec($ch);

            if ($response === false) {
                $error = curl_error($ch);
                throw new Exception('cURL error: ' . $error, curl_errno($ch));
            }

            $info = curl_getinfo($ch);
            $httpCode = (int) $info['http_code'];

            curl_close($ch);

            // Decodificar SIEMPRE como JSON
            $data = json_decode($response, true);

            if ($data === null) {
                // Loguea la respuesta raw para saber qué llegó
                log_message('error', 'Flow createOrder - respuesta no JSON: ' . $response);
                throw new Exception('Error decoding JSON response: ' . json_last_error_msg());
            }

            // Si el código HTTP NO es 200, asumimos error de Flow
            if ($httpCode !== 200) {
                // Muchos servicios de Flow responden algo tipo {"code":123,"message":"firma inválida"}
                $flowCode = $data['code'] ?? null;
                $flowMessage = $data['message'] ?? 'Error desconocido de Flow';

                log_message(
                    'error',
                    "Flow createOrder - HTTP $httpCode - code: {$flowCode} - message: {$flowMessage} - response: " . $response
                );

                throw new Exception("Flow API error ($httpCode): {$flowMessage}", $httpCode);
            }

            // En 200 DEBE venir url y token
            if (!isset($data['url'], $data['token'])) {
                log_message('error', 'Flow createOrder - faltan url/token en respuesta 200: ' . $response);
                throw new Exception('La API no devolvió url/token en respuesta 200');
            }

            $finalUrl = $data['url'] . '?token=' . urlencode($data['token']);

            return redirect()->to($finalUrl);
        } catch (Exception $e) {
            // Aquí puedes devolver una vista amigable en lugar de echo
            log_message('error', 'Error en createOrder: ' . $e->getCode() . ' - ' . $e->getMessage());
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
                }

                $API->disconnect(); // Desconectar de la API
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



    public function confirmation()
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

            $myData = [
                'flow_order' => $json_response['flowOrder'],
                'commerceOrder' => $json_response['commerceOrder'],
                'requestDate' => $json_response['requestDate'],
                'status' => $json_response['status'],
                'subject' => $json_response['subject'],
                'currency' => $json_response['currency'],
                'amount' => $json_response['amount'],
                'payer' => $json_response['payer'],
                'ip' => $json_response['optional']['ip'],
                'mac' => $json_response['optional']['mac']
            ];
        } catch (Exception $e) {
            echo 'Error: ' . $e->getCode() . ' - ' . $e->getMessage();
        }

        return view('confirmation', $myData);
    }
}
