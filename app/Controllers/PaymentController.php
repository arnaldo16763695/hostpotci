<?php

namespace App\Controllers;

// use App\Models\ClientsModel;
// use App\Libraries\RouterosAPI;

use App\Models\OrdersModel;
use  Exception;
// use App\Libraries\RouterosAPI;

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
        $url = 'https://sandbox.flow.cl/api';
        $url = $url . '/payment/create';

        $secretKey = 'f798565f885a0a94e2aed2b04ba7fccd0f9a72bd';
        $apikey = '56349F7B-7FAE-424C-921C-70A982BL39A1';




        $textPlan = '';
        if ($post['plan'] === '1000') {
            $textPlan = '1 hora';
        } elseif ($post['plan'] === '3000') {
            $textPlan = '2 días';
        } else {
            $textPlan = '7 días';
        }

        $subject = 'Conexión a internet plan de: ' . $textPlan;
        //optional
        $currency = 'CLP';
        $amount = $post['plan'];
        //$email
        $email = $post['email'];



        $optional = json_encode([
            'ip' => $post['ip'],
            'mac' => $post['mac']
        ]);

        $urlConfirmation = 'https://ajedev.com';
        $urlReturn = 'https://vit.gob.ve';
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
}
