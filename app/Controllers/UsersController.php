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
            $url = base_url('create-order-payment') . '?' . http_build_query([
                'ip'  => $ip,
                'mac' => $macUser,
            ]);

            return redirect()->to($url);
        }
    }

    public function createUserMikrotik()
    {
        print_r($_POST);
        exit;
    }
}
