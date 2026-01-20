<?php

namespace App\Controllers;

use App\Models\OrdersModel;
use  Exception;
use App\Libraries\RouterosAPI;

class PaymentController extends BaseController
{

    public function index()
    {
        $ip = $this->request->getPost('ip');
        $mac = $this->request->getPost('mac');
        return  view('home-payment', [
            'ip' => $ip,
            'mac' => $mac,
        ]);
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
            // 'phone' => 'required|max_length[100]',
            'phone' => 'required|regex_match[/^9\d{8}$/]',
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
            'phone' => $post['phone'],
            'plan' => $post['plan'],
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

                try {
                    $orderM = new OrdersModel();
                    // Actualizar la orden en tu BD
                    $orderM->where('codeOrder', $json_response['commerceOrder'])
                        ->set([
                            'status' => 'PAGADA',
                            'email'  => $json_response['payer'],
                        ])
                        ->update();
                } catch (\Throwable $e) {
                    log_message('error', 'DB insert update: ' . $e->getMessage());
                }

                $payload = [
                    'email' => $json_response['payer'],
                    'phone' => $json_response['optional']['phone'],
                    'plan'  => $json_response['amount'],
                    'mac'  => $json_response['optional']['mac'],
                    'ip'    => $json_response['optional']['ip'],
                ];


                //  $userName = trim($this->request->getPost('phone'));
                $plan = $payload['plan'];
                $userProfile = '';

                switch ($plan) {
                    case '1000':
                        $userProfile = 'perfil_1000';
                        break;
                    case '3000':
                        $userProfile = 'perfil_3000';
                        break;
                    case '5000':
                        $userProfile = 'perfil_5000';
                        break;
                    case '10000':
                        $userProfile = 'perfil_10000';
                        break;
                }


                // ----- Login en Mikrotik -----
                $ip       = env('ip_mikrotik');
                $username = env('username_mikrotik');
                $password = env('password_mikrotik');
                $port     = env('port_mikrotik');
                $API        = new RouterosAPI();
                $API->debug = false;
                $API->port  = $port;
                $hotspotServ = env('serv_hotspot');
                // $mkconnec = [];

                if (!$API->connect($ip, $username, $password)) {
                    log_message('error', 'No se pudo conectar a Mikrotik en createUserMikrotik()');
                    return redirect()->back()->withInput()->with('errors', 'No se pudo conectar al router.');
                }

                try {
                    // 1) Buscar si ya existe
                    $userExist = $API->comm('/ip/hotspot/user/print', [
                        '?name'      => $payload['phone'],
                        '.proplist'  => '.id,name,profile,server'
                    ]);

                    // Si tu lib devuelve !trap, lo logueamos
                    if (isset($userExist['!trap'])) {
                        log_message('error', 'MikroTik user/print trap: ' . $userExist['!trap'][0]['message']);
                        $userExist = [];
                    }

                    if (!empty($userExist) && isset($userExist[0]['.id'])) {
                        // 2) Si existe: actualizar (perfil/server/pass)
                        $setRes = $API->comm('/ip/hotspot/user/set', [
                            '.id'      => $userExist[0]['.id'],
                            'server'   => $hotspotServ,
                            'password' => $payload['phone'],
                            'profile'  => $userProfile,
                        ]);

                        if (isset($setRes['!trap'])) {
                            log_message('error', 'MikroTik user/set trap: ' . $setRes['!trap'][0]['message']);
                        } else {
                            log_message('info', "MikroTik user updated: {$payload['phone']}");
                        }
                    } else {
                        // 3) Si no existe: crear
                        $addRes = $API->comm('/ip/hotspot/user/add', [
                            'server'   => $hotspotServ,
                            'name'     => $payload['phone'],
                            'password' => $payload['phone'],
                            'profile'  => $userProfile,
                        ]);

                        if (isset($addRes['!trap'])) {
                            log_message('error', 'MikroTik user/add trap: ' . $addRes['!trap'][0]['message']);
                        } else {
                            log_message('info', "MikroTik user created: {$payload['phone']}");
                        }
                    }

                    // 4) Login (misma conexión)
                    $loginParams = [
                        'user'     => $payload['phone'],
                        'password' => $payload['phone'],
                        'ip' => $payload['ip'],
                    ];

                    $loginRes = $API->comm('/ip/hotspot/active/login', $loginParams);

                    if (isset($loginRes['!trap'])) {
                        log_message('error', 'Hotspot login trap: ' . $loginRes['!trap'][0]['message']);
                    } else {
                        log_message('info', 'Hotspot login OK user=' . $payload['phone'] . ' ip=' . ($ipUser ?? ''));
                    }

                    // 5) Scheduler expiración continua (misma conexión)
                    $delay = $this->planToDelay($plan); // 1000/3000/5000/10000 -> 1h/1d/2d/7d
                    $this->scheduleHotspotExpiry($API, $payload['phone'], $delay);
                } catch (\Throwable $e) {
                    log_message('error', 'createUserMikrotik() exception: ' . $e->getMessage());
                    $API->disconnect();

                    return redirect()->back()->withInput()->with('errors', 'Ocurrió un error procesando la solicitud.');
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

    private function planToDelay(string $plan): string
    {
        return match ($plan) {
            '3000'  => '1d',
            '5000'  => '2d',
            '10000' => '7d',
            '1000'  => '1h',
            default => '1h',
        };
    }

    // private function scheduleHotspotExpiry(RouterosAPI $API, string $userName, string $delay): void
    // {
    //     $schedName = 'exp-' . $userName;

    //     // 1) obtener fecha/hora del router (evita desfase de zona horaria)
    //     $clock = $API->comm('/system/clock/print');
    //     $routerDate = $clock[0]['date'] ?? null; // ej: "jan/17/2026"
    //     $routerTime = $clock[0]['time'] ?? null; // ej: "11:07:21"

    //     if (!$routerDate || !$routerTime) {
    //         log_message('error', 'No se pudo leer /system/clock/print en MikroTik');
    //         return;
    //     }

    //     // 2) sumar 10 segundos a la hora del router
    //     [$h, $m, $s] = array_map('intval', explode(':', $routerTime));
    //     $s += 10;
    //     if ($s >= 60) {
    //         $s -= 60;
    //         $m += 1;
    //     }
    //     if ($m >= 60) {
    //         $m -= 60;
    //         $h += 1;
    //     }
    //     if ($h >= 24) {
    //         $h -= 24;
    //     } // si cruza de dia, para tus pruebas basta

    //     $startTime = sprintf('%02d:%02d:%02d', $h, $m, $s);

    //     // 3) borrar scheduler anterior si existía
    //     $old = $API->comm('/system/scheduler/print', [
    //         '?name' => $schedName,
    //         '.proplist' => '.id'
    //     ]);
    //     if (!empty($old[0]['.id'])) {
    //         $API->comm('/system/scheduler/remove', [
    //             '.id' => $old[0]['.id']
    //         ]);
    //     }

    //     // 4) script: espera delay y expira al usuario
    //     $onEvent =
    //         ':log warning ("EXP-START user=' . $userName . ' delay=' . $delay . '"); ' .
    //         ':delay ' . $delay . '; ' .
    //         ':log warning ("EXP-KILL user=' . $userName . '"); ' .
    //         '/ip hotspot active remove [find user="' . $userName . '"]; ' .
    //         '/ip hotspot user remove [find name="' . $userName . '"]; ' .
    //         '/system scheduler remove [find name="' . $schedName . '"];';

    //     // 5) crear scheduler
    //     $API->comm('/system/scheduler/add', [
    //         'name'       => $schedName,
    //         'start-date' => $routerDate,
    //         'start-time' => $startTime,
    //         'interval'   => $delay,               // no importa, se auto-borra
    //         'policy'     => 'read,write,test',  // write necesario para borrar user
    //         'on-event'   => $onEvent,
    //         'comment'    => 'Auto-expire hotspot user',
    //     ]);

    //     log_message('info', "Scheduler creado: {$schedName} start {$routerDate} {$startTime} delay {$delay}");
    // }

    private function scheduleHotspotExpiry(RouterosAPI $API, string $userName, string $delay): void
    {
        $schedName = 'exp-' . $userName;

        // 1) leer hora del router
        $clock = $API->comm('/system/clock/print');
        $routerDate = $clock[0]['date'] ?? null; // ej: "jan/19/2026"
        $routerTime = $clock[0]['time'] ?? null; // ej: "22:31:05"

        if (!$routerDate || !$routerTime) {
            log_message('error', 'No se pudo leer /system/clock/print');
            return;
        }

        // 2) sumar 5 segundos
        [$h, $m, $s] = array_map('intval', explode(':', $routerTime));
        $s += 5;
        if ($s >= 60) {
            $s -= 60;
            $m += 1;
        }
        if ($m >= 60) {
            $m -= 60;
            $h += 1;
        }
        if ($h >= 24) {
            $h -= 24;
        } // para tu caso basta

        $startTime = sprintf('%02d:%02d:%02d', $h, $m, $s);

        // 3) borrar scheduler anterior si existe
        $old = $API->comm('/system/scheduler/print', ['?name' => $schedName, '.proplist' => '.id']);
        if (!empty($old) && !empty($old[0]['.id'])) {
            $API->comm('/system/scheduler/remove', ['.id' => $old[0]['.id']]);
        }

        // 4) evento
        $onEvent =
            ':log warning ("EXP-START user=' . $userName . ' delay=' . $delay . '"); ' .
            ':delay ' . $delay . '; ' .
            ':log warning ("EXP-KILL user=' . $userName . '"); ' .
            '/ip hotspot active remove [find user="' . $userName . '"]; ' .
            '/ip hotspot user remove [find name="' . $userName . '"]; ' .
            '/system scheduler remove [find name="' . $schedName . '"];';

        // 5) crear scheduler (una sola vez)
        $API->comm('/system/scheduler/add', [
            'name'       => $schedName,
            'start-date' => $routerDate,
            'start-time' => $startTime,
            'interval'   => '0s',
            'policy'     => 'read,write,test',
            'on-event'   => $onEvent,
            'comment'    => 'Auto-expire hotspot user',
            'disabled'   => 'no',
        ]);

        log_message('info', "Scheduler creado {$schedName} start {$routerDate} {$startTime} delay {$delay}");
    }
}
