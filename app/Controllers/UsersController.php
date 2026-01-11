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

    public function sendNotification()
    {
        $rules = [
            'name' => [
                'rules' => 'required|min_length[3]|max_length[100]|alpha_space',
                'errors' => [
                    'required'    => 'El nombre es obligatorio.',
                    'min_length'  => 'El nombre debe tener al menos 3 caracteres.',
                    'alpha_space' => 'El nombre solo puede contener letras y espacios.',
                ]
            ],
            'rut' => [
                'rules' => 'required|min_length[7]|max_length[12]',
                'errors' => [
                    'required'   => 'El RUT es obligatorio.',
                    'min_length' => 'El RUT parece demasiado corto.',
                ]
            ],
            'email' => [
                'rules' => 'required|valid_email|max_length[100]',
                'errors' => [
                    'required'    => 'El email es obligatorio.',
                    'valid_email' => 'Debe ingresar un email válido.',
                ]
            ],
            'phone' => [
                'rules' => 'required|min_length[8]|max_length[12]',
                'errors' => [
                    'required'   => 'El teléfono es obligatorio.',
                    'min_length' => 'Debe ingresar al menos 8 dígitos.',
                ]
            ],
            'plan' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Debe seleccionar un plan.',
                ]
            ],

        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->listErrors());
        }

        $post = $this->request->getPost();

        $data = $this->request->getPost([
            'name',
            'email',
            'phone',
            'rut',
            'plan',
        ]);

        // print_r($post);exit;

        // save in db
        $userTM = new UsersTransferenceModel();
        $userTM->insert($data);

        // Remove CSRF field from email table
        unset($post['csrf_test_name']);

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

        foreach ($post as $key => $value) {
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

        if ($email->send()) {

            // =========================
            // Send WhatsApp (NEW BLOCK)
            // =========================
            try {
                $apiUrl    = 'http://api.textmebot.com/send.php';
                $apiKey    = env('whatsapp_api_key');
                $recipient = env('recipient');

                // Avoid variable name collision with $email service
                $name      = $post['name']  ?? '';
                $userEmail = $post['email'] ?? '';
                $phone     = $post['phone'] ?? '';
                $plan      = $post['plan']  ?? '';

                $whatMessage  = "📡 *Nueva solicitud de Internet*\n\n";
                $whatMessage .= "👤 Nombre: {$name}\n";
                $whatMessage .= "📧 Email: {$userEmail}\n";
                $whatMessage .= "📞 Teléfono: {$phone}\n";
                $whatMessage .= "📦 Plan: {$plan}\n";


                $query = http_build_query([
                    'recipient' => $recipient,
                    'apikey'    => $apiKey,
                    'text'      => $whatMessage,
                ]);

                $url = $apiUrl . '?' . $query;

                // Fire and forget (do not block user flow)
                $waResponse = @file_get_contents($url);

                // Log response for debugging
                log_message('info', 'WhatsApp API response: ' . ($waResponse ?? 'no-response'));
            } catch (\Throwable $e) {
                // Don't break the flow if WhatsApp fails
                log_message('error', 'WhatsApp send failed: ' . $e->getMessage());
            }

            // Return success view (same as you had)
            return view('message', [
                'title' => 'Solicitud enviada',
                'message' => 'Hemos recibido tu solicitud para conexión a Internet. 
                  Un asesor revisará tus datos y te contactará con las instrucciones 
                  para realizar la transferencia.'
            ]);
        } else {
            log_message('error', $email->printDebugger(['headers', 'subject', 'body']));

            return view('error-correo', [
                'title' => 'Ocurrió un problema',
                'message' => 'No pudimos enviar tu solicitud en este momento. 
                  Por favor, intenta nuevamente en unos minutos 
                  o contáctanos por WhatsApp o teléfono.'
            ]);
        }
    }

    public function loginToMikrotik()
    {

        $rules = [
            'phone' => 'required|max_length[15]',
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

    public function createUserMikrotik()
    {
        $rules = [
            'phone' => 'required|max_length[15]',
            'ip' => 'required|valid_ip',
            'plan' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->listErrors());
        }

        $post = $this->request->getPost();

        $payload = [
            'name'  => $post['name'],
            'email' => $post['email'],
            'phone' => $post['phone'],
            'rut'   => $post['rut'],
            'plan'  => $post['plan'],
            'mac'  => $post['mac'],
            'ip'    => $post['ip'] ?? null,
        ];


        $userName = trim($this->request->getPost('phone'));
        $plan = trim($this->request->getPost('plan'));
        $limitUptime = '';

        switch ($plan) {
            case '1000':
                $limitUptime = '00:03:00';
                break;
            case '3000':
                $limitUptime = '24:00:00';
                break;
            case '5000':
                $limitUptime = '48:00:00';
                break;
            case '10000':
                $limitUptime = '168:00:00';
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

        $userExist = [];

        if ($API->connect($ip, $username, $password)) {

            $userExist = $API->comm('/ip/hotspot/user/print', [
                '?name'        => $userName,
            ]);
            // print_r($userExist);
            // exit;
            if (isset($userExist[0]['.id'])) {

                // check if user is active in hotspot
                if ($userExist[0]['limit-uptime'] !== $userExist[0]['uptime']) {

                    // Construir URL con parámetros GET
                    $url = base_url('message-user-login') . '?' . http_build_query([
                        'ip'  => $payload['ip'],
                        'mac' => $payload['mac'],
                    ]);
                    session()->setFlashdata('user_loged', (string) 'Aún tienes una sessión activa, inicia con tu usuario');
                    return redirect()->to($url);
                }

                //reset counter
                $API->comm('/ip/hotspot/user/reset-counters', [
                    '.id'          => $userExist[0]['.id'],
                ]);
            } else {
                //create user in mikrotik
                $API->comm('/ip/hotspot/user/add', [
                    'server'      => 'ServHostpot',
                    'name'        => $userName,
                    'password'        => $userName,
                    'profile'        => 'test',
                ]);
            }


            //set limit-uptime user in mikrotik
            // find user
            $res = $API->comm('/ip/hotspot/user/print', [
                '?name' => $userName,
                '.proplist' => '.id,name,limit-uptime'
            ]);

            if (empty($res)) {
                log_message('error', "Hotspot user not found: $userName");
                return;
            }

            $userId = $res[0]['.id'];

            // Setear limit-uptime
            $API->comm('/ip/hotspot/user/set', [
                '.id'          => $userId,
                'limit-uptime' => $limitUptime,
            ]);

            log_message('info', "limit-uptime updated for $userName to $limitUptime");


            //Connect user

            $dataToConnection = [
                'user'  => trim($post['phone']), // this is phone but used as user 
                'password' => trim($post['phone']), // this is phone but used as password
                'ip'    => $post['ip'] ?? null,
            ];

            $this->loginHotspot($dataToConnection);

            //send whatsapp
            $this->sendWhatsApp(env('recipient'), $this->buildAdminWhatsApp($payload));


            $API->disconnect();
        } else {
            log_message('error', 'No se pudo conectar a Mikrotik en confirmation()');
        }

        return redirect()->to('https://google.com');
    }

    private function sendWhatsApp(string $recipient, string $message): void
    {
        try {
            $query = http_build_query([
                'recipient' => $recipient,
                'apikey'    => env('WHATSAPP_API_KEY'),
                'text'      => $message,
            ]);

            @file_get_contents(
                'http://api.textmebot.com/send.php?' . $query
            );

            log_message('info', 'WhatsApp sent to ' . $recipient);
        } catch (\Throwable $e) {
            log_message('error', 'WhatsApp failed: ' . $e->getMessage());
        }
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
}
