<?php

namespace App\Controllers;

use App\Models\UsersTransferenceModel;
use App\Libraries\RouterosAPI;

class UsersController extends BaseController
{
    protected $helpers = ['form'];
    public function index($email = null, $plan = null)
    {

        $email = $this->request->getGet('email');
        $plan  = $this->request->getGet('plan');
        $phone  = $this->request->getGet('phone');
        $mac  = $this->request->getGet('mac');
        $ip  = $this->request->getGet('ip');


        return view('contact-transference', [
            'email' => $email,
            'plan'  => $plan,
            'phone'  => $phone,
            'mac'  => $mac,
            'ip'  => $ip,
        ]);
    }


    public function loginToMikrotik()
    {

        $rules = [
            // 'phone' => 'required|max_length[15]',
            'phone' => 'required|regex_match[/^9\d{8}$/]',
            'ip' => 'required|valid_ip',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->listErrors());
        }

        $userLog = trim($this->request->getPost('phone'));
        $passwordLog = trim($this->request->getPost('phone')); // password will be the same phone
        $ipUser = $this->request->getPost('ip');
        $macUser = $this->request->getPost('mac');

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
                'user'        => $userLog,
                'password'    => $passwordLog,
                // 'mac-address' => $json_response['optional']['mac'] ?? null,
                'ip'          => $ipUser ?? null,
            ]);
            $API->disconnect();
        } else {
            log_message('error', 'No se pudo conectar a Mikrotik en confirmation()');
        }

        if (isset($mkconnec['!trap'])) {

            // $errorMessage = $mkconnec['!trap'][0]['message'] ?? 'Hotspot login error';
            $errorMessage = $mkconnec['!trap'][0]['message'] ?? 'Hotspot error';

            log_message('error', 'Error hotspot login: ' . $errorMessage);

            // Guardar datos relevantes en session (flashdata)
            session()->setFlashdata('hotspot_error', (string) $errorMessage);

            // Construir URL con parámetros GET
            $url = base_url('message-user-login') . '?' . http_build_query([
                'ip'  => $ipUser,
                'mac' => $macUser,
            ]);

            return redirect()->to($url);
        }
    }

    public function messageUserLogin()
    {

        $ip = $this->request->getGet('ip');
        $mac = $this->request->getGet('mac');

        return view('message-user-login', [
            'ip' => $ip,
            'mac' => $mac,
        ]);
    }

    // public function createUserMikrotik()
    // {
    //     $rules = [
    //         // 'phone' => 'required|max_length[15]',
    //         'phone' => 'required|regex_match[/^9\d{8}$/]',
    //         'ip' => 'required|valid_ip',
    //         'plan' => 'required',
    //     ];

    //     if (!$this->validate($rules)) {
    //         return redirect()->back()->withInput()->with('errors', $this->validator->listErrors());
    //     }

    //     $post = $this->request->getPost();

    //     $payload = [
    //         'name'  => $post['name'],
    //         'email' => $post['email'],
    //         'phone' => $post['phone'],
    //         'rut'   => $post['rut'],
    //         'plan'  => $post['plan'],
    //         'mac'  => $post['mac'],
    //         'ip'    => $post['ip'] ?? null,
    //     ];

    //     //save in db
    //     $userTM = new UsersTransferenceModel();
    //     $userTM->insert($payload);


    //     $userName = trim($this->request->getPost('phone'));
    //     $plan = trim($this->request->getPost('plan'));
    //     $userProfile = '';

    //     switch ($plan) {
    //         case '1000':
    //             $userProfile = 'perfil_1000';
    //             break;
    //         case '3000':
    //             $userProfile = 'perfil_3000';
    //             break;
    //         case '5000':
    //             $userProfile = 'perfil_5000';
    //             break;
    //         case '10000':
    //             $userProfile = 'perfil_10000';
    //             break;
    //     }


    //     // ----- Login en Mikrotik -----
    //     $ip       = env('ip_mikrotik');
    //     $username = env('username_mikrotik');
    //     $password = env('password_mikrotik');
    //     $port     = env('port_mikrotik');
    //     $API        = new RouterosAPI();
    //     $API->debug = false;
    //     $API->port  = $port;
    //     $hotspotServ = env('serv_hotspot');

    //     $userExist = [];

    //     if ($API->connect($ip, $username, $password)) {

    //         $userExist = $API->comm('/ip/hotspot/user/print', [
    //             '?name'        => $userName,
    //         ]);


    //         //create user in mikrotik
    //         $API->comm('/ip/hotspot/user/add', [
    //             'server'      => $hotspotServ,
    //             'name'        => $userName,
    //             'password'        => $userName,
    //             'profile'        =>  $userProfile,
    //         ]);

    //         //Connect user

    //         $dataToConnection = [
    //             'user'  => trim($post['phone']), // this is phone but used as user 
    //             'password' => trim($post['phone']), // this is phone but used as password
    //             'ip'    => $post['ip'] ?? null,
    //         ];

    //         $this->loginHotspot($dataToConnection);

    //         //create scheduler
    //         $delay = $this->planToDelay($plan); // 3000/5000/10000
    //         $this->scheduleHotspotExpiry($API, $payload['phone'], $delay);

    //         //send whatsapp
    //         $this->sendWhatsApp(env('recipient'), $this->buildAdminWhatsApp($payload));

    //         //sed email admin
    //         $this->sendEmailToAdmin($payload);

    //         //sed email to client
    //         $this->sendEmailToCliente($payload);




    //         $API->disconnect();
    //     } else {
    //         log_message('error', 'No se pudo conectar a Mikrotik en confirmation()');
    //     }

    //     return redirect()->to('https://google.com');
    // }

    public function createUserMikrotik()
    {
        $rules = [
            'phone' => [
                'rules'  => 'required|regex_match[/^9\d{8}$/]',
                'errors' => [
                    'required'    => 'El teléfono es obligatorio.',
                    'regex_match' => 'Ingrese un número celular válido (ej: 9XXXXXXXX).',
                ]
            ],
            'ip' => [
                'rules'  => 'required|valid_ip',
                'errors' => [
                    'required' => 'La IP es obligatoria.',
                    'valid_ip' => 'La IP no es válida.',
                ]
            ],
            'plan' => [
                'rules'  => 'required|in_list[1000,3000,5000,10000]',
                'errors' => [
                    'required' => 'Debe seleccionar un plan.',
                    'in_list'  => 'Plan inválido.',
                ]
            ],
            // Si estos campos siempre vienen en tu POST, vale la pena validarlos:
            'mac' => [
                'rules'  => 'required|regex_match[/^([A-Fa-f0-9]{2}:){5}[A-Fa-f0-9]{2}$/]',
                'errors' => [
                    'required'    => 'La MAC es obligatoria.',
                    'regex_match' => 'La MAC no es válida (XX:XX:XX:XX:XX:XX).',
                ]
            ],
            'email' => [
                'rules'  => 'permit_empty|valid_email|max_length[100]',
                'errors' => [
                    'valid_email' => 'Debe ingresar un email válido.',
                ]
            ],
            'name' => [
                'rules'  => 'permit_empty|min_length[3]|max_length[100]|alpha_space',
                'errors' => [
                    'min_length'  => 'El nombre debe tener al menos 3 caracteres.',
                    'alpha_space' => 'El nombre solo puede contener letras y espacios.',
                ]
            ],
            'rut' => [
                'rules'  => 'permit_empty|min_length[7]|max_length[12]',
                'errors' => [
                    'min_length' => 'El RUT parece demasiado corto.',
                ]
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->listErrors());
        }

        $post = $this->request->getPost();

        $userName = trim($post['phone']); // user = phone
        $plan     = trim($post['plan']);
        $ipUser   = $post['ip'] ?? null;

        // Plan -> profile
        $userProfile = match ($plan) {
            '1000'  => 'perfil_1000',
            '3000'  => 'perfil_3000',
            '5000'  => 'perfil_5000',
            '10000' => 'perfil_10000',
            default => '',
        };

        if (empty($userProfile)) {
            log_message('error', 'Plan sin perfil. plan=' . json_encode($plan));
            return redirect()->back()->withInput()->with('errors', 'Plan inválido.');
        }

        // Payload para tus notificaciones
        $payload = [
            'name'  => $post['name']  ?? '',
            'email' => $post['email'] ?? '',
            'phone' => $post['phone'],
            'rut'   => $post['rut']   ?? '',
            'plan'  => $post['plan'],
            'mac'   => $post['mac'],
            'ip'    => $post['ip'] ?? null,
        ];

        // Guardar en DB (tal como lo tienes)
        try {
            $userTM = new UsersTransferenceModel();
            $userTM->insert($payload);
        } catch (\Throwable $e) {
            log_message('error', 'DB insert failed createUserMikrotik(): ' . $e->getMessage());
            // Puedes decidir si continúas o cortas
        }

        // MikroTik connection
        $ip       = env('ip_mikrotik');
        $username = env('username_mikrotik');
        $password = env('password_mikrotik');
        $port     = (int) env('port_mikrotik');
        $hotspotServ = env('serv_hotspot');

        $API = new RouterosAPI();
        $API->debug = false;
        $API->port  = $port;

        if (!$API->connect($ip, $username, $password)) {
            log_message('error', 'No se pudo conectar a Mikrotik en createUserMikrotik()');
            return redirect()->back()->withInput()->with('errors', 'No se pudo conectar al router.');
        }

        try {
            // 1) Buscar si ya existe
            $userExist = $API->comm('/ip/hotspot/user/print', [
                '?name'      => $userName,
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
                    'password' => $userName,
                    'profile'  => $userProfile,
                ]);

                if (isset($setRes['!trap'])) {
                    log_message('error', 'MikroTik user/set trap: ' . $setRes['!trap'][0]['message']);
                } else {
                    log_message('info', "MikroTik user updated: {$userName}");
                }
            } else {
                // 3) Si no existe: crear
                $addRes = $API->comm('/ip/hotspot/user/add', [
                    'server'   => $hotspotServ,
                    'name'     => $userName,
                    'password' => $userName,
                    'profile'  => $userProfile,
                ]);

                if (isset($addRes['!trap'])) {
                    log_message('error', 'MikroTik user/add trap: ' . $addRes['!trap'][0]['message']);
                } else {
                    log_message('info', "MikroTik user created: {$userName}");
                }
            }

            // 4) Login (misma conexión)
            $loginParams = [
                'user'     => $userName,
                'password' => $userName,
            ];
            if (!empty($ipUser)) {
                $loginParams['ip'] = $ipUser; // IMPORTANTE: tu parámetro correcto es "ip"
            }

            $loginRes = $API->comm('/ip/hotspot/active/login', $loginParams);

            if (isset($loginRes['!trap'])) {
                log_message('error', 'Hotspot login trap: ' . $loginRes['!trap'][0]['message']);
            } else {
                log_message('info', 'Hotspot login OK user=' . $userName . ' ip=' . ($ipUser ?? ''));
            }

            // 5) Scheduler expiración continua (misma conexión)
            $delay = $this->planToDelay($plan); // 1000/3000/5000/10000 -> 1h/1d/2d/7d
            $this->scheduleHotspotExpiry($API, $userName, $delay);

            // 6) Notificaciones (no rompen el flujo)
            $this->sendWhatsApp(env('recipient'), $this->buildAdminWhatsApp($payload));
            $this->sendEmailToAdmin($payload);
            $this->sendEmailToCliente($payload);
        } catch (\Throwable $e) {
            log_message('error', 'createUserMikrotik() exception: ' . $e->getMessage());
            $API->disconnect();

            return redirect()->back()->withInput()->with('errors', 'Ocurrió un error procesando la solicitud.');
        }

        $API->disconnect();

        // Redirigir fuera del sitio
        return redirect()->to('https://google.com');
    }


    private function sendWhatsApp(string $recipient, string $message): void
    {
        // Normalizar número (TextMeBot no acepta +)
        $recipient = ltrim($recipient, '+');

        if (trim($message) === '') {
            log_message('error', 'WhatsApp message vacío');
            return;
        }

        $apiKey = env('whatsapp_api_key');

        if (!$apiKey) {
            log_message('error', 'WHATSAPP_API_KEY no definida');
            return;
        }

        $query = http_build_query([
            'recipient' => $recipient,
            'apikey'    => $apiKey,
            'text'      => $message,
        ]);

        $url = 'http://api.textmebot.com/send.php?' . $query;

        // Log útil pero sin exponer la API key
        log_message('info', 'WhatsApp send to ' . $recipient);

        $response = @file_get_contents($url);

        if ($response === false) {
            log_message('error', 'WhatsApp request failed');
            return;
        }

        log_message('info', 'WhatsApp response: ' . strip_tags($response));
    }



    private function buildAdminWhatsApp(array $p): string
    {
        $msg  = "📡 *Nueva activación de Internet*\n\n";
        $msg .= "👤 Nombre: {$p['name']}\n";
        $msg .= "📧 Email: {$p['email']}\n";
        $msg .= "📞 Teléfono: {$p['phone']}\n";
        $msg .= "📦 Plan: {$p['plan']}\n";
        $msg .= "💻 MAC: {$p['mac']}\n";

        if (!empty($p['ip'])) {
            $msg .= "🌐 IP: {$p['ip']}\n";
        }

        return $msg;
    }

    private function buildClientWhatsApp(array $p): string
    {
        return
            "📡 *Gracias por tu solicitud de Internet*\n\n" .
            "Hola *{$p['name']}*, gracias por preferir *Movinet Comunicaciones* 🙌\n\n" .
            "Para continuar con la activación, realiza la transferencia:\n\n" .
            "🏢 *MOVINET COMUNICACIONES SPA*\n" .
            "🆔 RUT: 77.008.345-1\n" .
            "🏦 Mercado Pago\n" .
            "💳 Cuenta Vista\n" .
            "🔢 N° de cuenta: 1075053672\n" .
            "📧 Correo: ventas@globalsi.cl\n\n" .
            "📦 *Plan seleccionado:* {$p['plan']}\n\n" .
            "👉 Una vez realizada la transferencia, *envía el comprobante a este WhatsApp*.\n\n" .
            "¡Quedamos atentos! 😊";
    }

    private function loginHotspot(array $p): void
    {
        $API = new RouterosAPI();
        $API->debug = false;
        $API->port  = env('port_mikrotik');

        if (!$API->connect(
            env('ip_mikrotik'),
            env('username_mikrotik'),
            env('password_mikrotik')
        )) {
            log_message('error', 'No se pudo conectar a Mikrotik');
            return;
        }

        $params = [
            'user'     => $p['user'],
            'password' => $p['password'],
            'ip' => $p['ip'],
        ];

        $res = $API->comm('/ip/hotspot/active/login', $params);
        $API->disconnect();

        if (isset($res['!trap'])) {
            log_message('error', 'Hotspot login error: ' . $res['!trap'][0]['message']);
            log_message('error', 'Params sent: ' . json_encode($params));
        } else {
            log_message('info', 'Hotspot login OK: ' . json_encode($params));
        }
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






    private function sendEmailToAdmin(array $data): void
    {
        // Build email HTML message (your same logic)
        $message = '
    <p style="font-family: Arial, sans-serif; font-size: 15px; color: #333;">
        Hola, estoy escribiendo para solicitar una <strong>conexión a Internet</strong>. 
        Estos son mis datos:
    </p>

    <table cellpadding="10" cellspacing="0" width="100%" 
        style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px;">

        <thead>
            <tr style="background-color: #004aad; color: #fff; text-align: left;">
                <th style="padding: 10px; width: 30%;">Campo</th>
                <th style="padding: 10px;">Valor</th>
            </tr>
        </thead>

        <tbody>
    ';

        foreach ($data as $key => $value) {
            $label = ucwords(str_replace('_', ' ', $key));

            $message .= "
            <tr style='border-bottom: 1px solid #ddd;'>
                <td style='font-weight: bold; padding: 8px; background:#f8f8f8;'>{$label}</td>
                <td style='padding: 8px;'>{$value}</td>
            </tr>
        ";
        }

        $message .= '
        </tbody>
    </table>
    ';

        // Send email (keep your current way)
        $email = service('email');
        $email->setTo(env('setToEmail'));
        $email->setSubject('Deseo conectarme al hotspot');
        $email->setMessage($message);

        $email->send();
    }

    private function sendEmailToCliente(array $data): void
    {
        // Mapeo simple (por claridad)
        $name  = $data['name']  ?? '';
        $emailClient = $data['email'] ?? '';
        $plan  = $data['plan']  ?? '';

        // Puedes mapear el plan a algo más amigable si quieres
        $planLabel = match ($plan) {
            '1000'  => '1 Hora de Internet ($1.000)',
            '3000'  => '1 Día de Internet ($3.000)',
            '5000'  => '2 Días de Internet ($5.000)',
            '10000' => '7 Días de Internet ($10.000)',
            default => 'Plan seleccionado',
        };

        // Mensaje HTML para el cliente
        $message = '
    <p style="font-family: Arial, sans-serif; font-size: 15px; color: #333;">
        Hola <strong>' . esc($name) . '</strong>,
    </p>

    <p style="font-family: Arial, sans-serif; font-size: 15px; color: #333;">
        Gracias por tu interés en nuestro servicio de <strong>Internet</strong>.
        A continuación te dejamos los datos para realizar la transferencia correspondiente
        al siguiente servicio:
    </p>

    <p style="font-family: Arial, sans-serif; font-size: 15px; color: #333;">
        <strong>📦 Servicio contratado:</strong><br>
        ' . esc($planLabel) . '
    </p>

    <table cellpadding="8" cellspacing="0" width="100%"
        style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px; margin-top: 10px;">

        <tbody>
            <tr>
                <td style="font-weight: bold; background:#f5f5f5;">Empresa</td>
                <td>MOVINET COMUNICACIONES SPA</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background:#f5f5f5;">RUT</td>
                <td>77.008.345-1</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background:#f5f5f5;">Banco</td>
                <td>Mercado Pago</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background:#f5f5f5;">Tipo de cuenta</td>
                <td>Cuenta Vista</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background:#f5f5f5;">N° de cuenta</td>
                <td>1075053672</td>
            </tr>
            <tr>
                <td style="font-weight: bold; background:#f5f5f5;">Correo</td>
                <td>ventas@globalsi.cl</td>
            </tr>
        </tbody>
    </table>

    <p style="font-family: Arial, sans-serif; font-size: 15px; color: #333; margin-top: 15px;">
        👉 <strong>Importante:</strong> Una vez realizada la transferencia, por favor envía
        el <strong>comprobante de pago</strong> para agilizar la activación de tu servicio.
    </p>

    <p style="font-family: Arial, sans-serif; font-size: 15px; color: #333;">
        Puedes enviarlo preferiblemente por <strong>WhatsApp</strong> al número:<br>
        📲 <a href="https://wa.me/56976452046" target="_blank">+56 9 7645 2046</a>
    </p>

    <p style="font-family: Arial, sans-serif; font-size: 15px; color: #333;">
        Quedamos atentos para activar tu servicio a la brevedad.<br>
        <strong>Movinet Comunicaciones</strong>
    </p>
    ';

        // Enviar correo
        $email = service('email');
        $email->setTo($emailClient);
        $email->setSubject('Datos para activar tu servicio de Internet');
        $email->setMessage($message);

        $email->send();
    }
}
