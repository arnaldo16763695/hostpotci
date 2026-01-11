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
        $mac  = $this->request->getGet('mac');
        $ip  = $this->request->getGet('ip');


        return view('contact-transference', [
            'email' => $email,
            'plan'  => $plan,
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
                // 'rules' => 'required|regex_match[/^9\d{8}$/]',
                'rules' => 'required',
                'errors' => [
                    'required' => 'El teléfono es obligatorio.',
                    'regex_match' => 'Ingrese un número celular válido (ej: 9XXXXXXXX).',
                ]
            ],
            'plan' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Debe seleccionar un plan.',
                ]
            ],
            'mac' => [
                'rules' => 'required|max_length[50]|regex_match[/^([A-Fa-f0-9]{2}:){5}[A-Fa-f0-9]{2}$/]',
                'errors' => [
                    'required'    => 'La MAC es obligatoria.',
                    'regex_match' => 'La dirección MAC no es válida (formato correcto: XX:XX:XX:XX:XX:XX).',
                ]
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->listErrors());
        }

        $post = $this->request->getPost();

        $payload = [
            'name'  => $post['name'],
            'email' => $post['email'],
            'phone' => env('country_code') . $post['phone'], 
            'rut'   => $post['rut'],
            'plan'  => $post['plan'],
            'ip'    => $post['ip'] ?? null,
            'mac'   => $post['mac'],
        ];

        $data = $this->request->getPost([
            'name',
            'email',
            'phone',
            'rut',
            'plan',
            'ip',
            'mac'
        ]);

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

            //what admin
            $this->sendWhatsApp(
                env('recipient'),
                $this->buildAdminWhatsApp($payload)
            );
            
            //Esto no esta funcionando debido a que la api exige que el destinatario debe estar registrado
            // //what client
            // $this->sendWhatsApp(
            //     $payload['phone'],
            //     $this->buildClientWhatsApp($payload)
            // );

            $this->loginHotspot($payload);

            // Return success view (same as you had)
            return view('message', [
                'title' => 'Solicitud enviada',
                'message' => '
        Hemos recibido tu solicitud para conexión a Internet.<br>
        Por favor envía el comprobante de la transferencia al WhatsApp:
        <a href="https://wa.me/56976452046" target="_blank">
            +56 9 7645 2046
        </a>
    '
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
            'user'     => 'user_' . $p['plan'],
            'password' => 'M0v1n3t20',
        ];

        /**
         * SOLO enviar IP si realmente existe
         * (si no, Mikrotik hace TRAP)
         */
        if (!empty($p['ip'])) {
            $params['ip'] = $p['ip'];
        }

        /**
         * MAC sí puedes enviarla siempre
         */
        if (!empty($p['mac'])) {
            $params['mac-address'] = $p['mac'];
        }

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
