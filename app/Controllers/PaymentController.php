<?php

namespace App\Controllers;

use App\Models\OrdersModel;
use  Exception;
use App\Libraries\RouterosAPI;

class PaymentController extends BaseController
{

    public function index()
    {
        // return  view('creater-order-payment'); 
        return  view('home-payment');
    }

    protected $helpers = ['form'];

    public function createOrderPayment()
    {
        $ip = $this->request->getGet('ip');
        $mac  = $this->request->getGet('mac');
        return view('creater-order-payment', [
            'ip' => $ip,
            'mac'  => $mac,
        ]);
    }

    public function createOrder()
    {
        $rules = [
            'email' => 'required|max_length[100]|valid_email',
            'phone' => 'required|max_length[100]',
            'ip' => 'required|valid_ip',
            'mac' => 'required',
        ];

        if (!$this->validate($rules)) {
            // return redirect()->back()->withInput()->with('errors', $this->validator->listErrors());
            return;
        }



        // we obtain the post variables
        $post = $this->request->getPost(['mac', 'ip', 'email', 'plan', 'phone']);

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
            'mac' => $post['mac'],
            'phone' => $post['phone']
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
        $url = env('url_apiflow') . '/payment/getStatus';

        // Flow normalmente manda el token a la urlReturn
        $token = $this->request->getPost('token') ?? $this->request->getGet('token') ?? null;

        if (!$token) {
            log_message('error', 'getstatuspayment sin token');
            return $this->response
                ->setStatusCode(400)
                ->setBody('Falta el token de pago.');
        }

        $params = [
            "apiKey" => env('apikey'),
            "token"  => $token,
        ];

        // Ordenar las keys
        $keys = array_keys($params);
        sort($keys);

        // Concatenación para la firma
        $toSign = '';
        foreach ($keys as $key) {
            $toSign .= $key . $params[$key];
        }

        $signature = hash_hmac('sha256', $toSign, env('secretKey'));
        $params["s"] = $signature;

        $url = $url . "?" . http_build_query($params);

        try {
            $json_response = $this->callFlowGetStatus($token);

            $status  = (int) ($json_response['status'] ?? 0);
            $amount  = $json_response['amount']        ?? null;
            $subject = $json_response['subject']       ?? '';
            $payer   = $json_response['payer']         ?? '';
            $commerceOrder = $json_response['commerceOrder'] ?? null;

            // Puedes leer tu BD si quieres confirmar:
            // $orderM = new OrdersModel();
            // $order  = $orderM->where('codeOrder', $commerceOrder)->first();       

            switch ($status) {
                case 2: // PAGADO
                    // En teoría, ya está logueado por confirmation().
                    // Aquí solo UX: redirigir o mostrar vista.
                    $myData = [
                        'flow_order'    => $json_response['flowOrder']      ?? null,
                        'commerceOrder' => $json_response['commerceOrder']  ?? null,
                        'requestDate'   => $json_response['requestDate']    ?? null,
                        'status'        => $json_response['status']         ?? null,
                        'subject'       => $json_response['subject']        ?? null,
                        'currency'      => $json_response['currency']       ?? null,
                        'amount'        => $json_response['amount']         ?? null,
                        'payer'         => $json_response['payer']          ?? null,
                        'ip'            => $json_response['optional']['ip'] ?? null,
                        'mac'           => $json_response['optional']['mac'] ?? null,
                    ];

                    // En este punto, confirmation() (el endpoint de Flow) ya debió haber
                    // actualizado la BD y logueado al usuario en Mikrotik.
                    // Aquí solo mostramos la vista bonita.
                    return view('confirmation', $myData);

                case 3: // RECHAZADO
                    return view('payment_result', [
                        'status'  => 'rejected',
                        'title'   => 'Pago rechazado',
                        'message' => 'El pago ha sido rechazado. Por favor, intente nuevamente.',
                        'amount'  => $amount,
                        'subject' => $subject,
                        'payer'   => $payer,
                    ]);

                case 4: // ANULADO
                    return view('payment_result', [
                        'status'  => 'canceled',
                        'title'   => 'Pago anulado',
                        'message' => 'El pago ha sido anulado. Si no reconoce esta operación, contacte soporte.',
                        'amount'  => $amount,
                        'subject' => $subject,
                        'payer'   => $payer,
                    ]);

                case 1: // PENDIENTE
                    return view('payment_result', [
                        'status'  => 'pending',
                        'title'   => 'Pago pendiente',
                        'message' => 'El pago está pendiente. Si ya completó el proceso, espere unos segundos y recargue la página.',
                        'amount'  => $amount,
                        'subject' => $subject,
                        'payer'   => $payer,
                    ]);

                default:
                    return view('payment_result', [
                        'status'  => 'unknown',
                        'title'   => 'Estado desconocido',
                        'message' => 'No pudimos determinar el estado del pago. Si el problema persiste, contacte soporte.',
                        'amount'  => $amount,
                        'subject' => $subject,
                        'payer'   => $payer,
                    ]);
            }
        } catch (Exception $e) {
            log_message('error', 'Error en getstatuspayment: ' . $e->getCode() . ' - ' . $e->getMessage());
            return $this->response
                ->setStatusCode(500)
                ->setBody('Error: ' . $e->getCode() . ' - ' . $e->getMessage());
        }
    }



    public function confirmation()
    {
        $url = env('url_apiflow') . '/payment/getStatus';

        // Flow suele mandar el token por POST (pero dejamos GET por si pruebas a mano)
        $post = $this->request->getPost();
        $token = $post['token'] ?? $this->request->getGet('token') ?? null;

        if (!$token) {
            log_message('error', 'Flow confirmation sin token');
            return $this->response->setStatusCode(400)->setBody('Missing token');
        }

        $params = [
            "apiKey" => env('apikey'),
            "token"  => $token,
        ];

        // Ordenar keys
        $keys = array_keys($params);
        sort($keys);

        // Concatenar para firmar
        $toSign = "";
        foreach ($keys as $key) {
            $toSign .= $key . $params[$key];
        }

        $signature = hash_hmac('sha256', $toSign, env('secretKey'));
        $params["s"] = $signature;

        $url = $url . "?" . http_build_query($params);

        try {
            $json_response = $this->callFlowGetStatus($token);

            // Si el pago está PAGADO (status 2), aquí hacemos TODO:
            if ((int)$json_response['status'] === 2) {
                $orderM = new OrdersModel();

                // Actualizar la orden en tu BD
                $orderM->where('codeOrder', $json_response['commerceOrder'])
                    ->set([
                        'status' => 'PAGADA',
                        'email'  => $json_response['payer'],
                    ])
                    ->update();

                // ----- Login en Mikrotik -----
                $ip       = env('ip_mikrotik');
                $username = env('username_mikrotik');
                $password = env('password_mikrotik');
                $port     = env('port_mikrotik');

                $API        = new RouterosAPI();
                $API->debug = false;
                $API->port  = $port;

                $mkconnec = [];

                if ($API->connect($ip, $username, $password)) {
                    $mkconnec = $API->comm('/ip/hotspot/active/login', [
                        'user'        => $json_response['optional']['phone'] ?? null,
                        'password'    => $json_response['optional']['phone'] ?? null,
                        // 'mac-address' => $json_response['optional']['mac'] ?? null,
                        'ip'          => $json_response['optional']['ip'] ?? null,
                    ]);
                    $API->disconnect();
                } else {
                    log_message('error', 'No se pudo conectar a Mikrotik en confirmation()');
                }

                if (isset($mkconnec['!trap'])) {
                    log_message('error', 'Error hotspot login: ' . $mkconnec['!trap'][0]['message']);
                    log_message('debug', 'phone: ' . ($json_response['optional']['phone'] ?? 'null'));
                    log_message('debug', 'ip: ' . ($json_response['optional']['ip'] ?? 'null'));
                }
            }
            return $this->response->setStatusCode(200)->setBody('OK');
        } catch (Exception $e) {
            log_message('error', 'Error en confirmation: ' . $e->getCode() . ' - ' . $e->getMessage());
            return $this->response
                ->setStatusCode(500)
                ->setBody('Error: ' . $e->getCode() . ' - ' . $e->getMessage());
        }
    }

    private function callFlowGetStatus(string $token): array
    {
        $url = env('url_apiflow') . '/payment/getStatus';

        $params = [
            'apiKey' => env('apikey'),
            'token'  => $token,
        ];

        $keys = array_keys($params);
        sort($keys);

        $toSign = '';
        foreach ($keys as $key) {
            $toSign .= $key . $params[$key];
        }

        $params['s'] = hash_hmac('sha256', $toSign, env('secretKey'));

        $url = $url . '?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception($error, 1);
        }

        $info = curl_getinfo($ch);
        curl_close($ch);

        if (!in_array((string)$info['http_code'], ['200', '400', '401'])) {
            throw new Exception(
                'Unexpected error occurred. HTTP_CODE: ' . $info['http_code'],
                (int)$info['http_code']
            );
        }

        $json = json_decode($response, true);
        if ($json === null) {
            log_message('error', 'Flow getStatus - JSON inválido: ' . $response);
            throw new Exception('Error decoding JSON response');
        }

        return $json;
    }
}
