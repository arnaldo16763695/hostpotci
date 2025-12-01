<?php

namespace App\Controllers;

use App\Models\UsersTransferenceModel;

class UsersController extends BaseController
{
    protected $helpers = ['form'];
    public function index($email = null, $plan = null)
    {

        $email = $this->request->getGet('email');
        $plan  = $this->request->getGet('plan');
        $mac  = $this->request->getGet('mac');


        return view('contact-transference', [
            'email' => $email,
            'plan'  => $plan,
            'mac'  => $mac,
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
                'rules' => 'required|min_length[7]|max_length[12]', // ideal validarlo con función personalizada
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
                    'required'  => 'El teléfono es obligatorio.',
                    'min_length' => 'Debe ingresar al menos 8 dígitos.',
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

        $data = $this->request->getPost([
            'name',
            'email',
            'phone',
            'rut',
            'plan'
        ]);

        // save in db
        $userTM = new UsersTransferenceModel();
        $userTM->insert($data);


        // Eliminar el campo que NO quieres enviar
        unset($post['csrf_test_name']);

        // Mensaje inicial + tabla
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

            // convertir keys a nombres más bonitos
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
        $email = service('email');
        // $email->setFrom('transferencias@movinet.cl', 'Your Name');
        $email->setTo(env('setToEmail'));
        // $email->setCC('aespinoza@globalsi.cl');
        // $email->setBCC('them@their-example.com');

        $email->setSubject('Deseo conectarme al hotspot');
        $email->setMessage($message);
        if ($email->send()) {
            // Opcional: guardar también en BD la solicitud aquí

            return view('message', [
                'title' => 'Solicitud enviada',
                'message' => 'Hemos recibido tu solicitud para conexión a Internet. 
                      Un asesor revisará tus datos y te contactará con las instrucciones 
                      para realizar la transferencia.'
            ]);
        } else {
            // Loguear el error y mostrar algo amigable
            log_message('error', $email->printDebugger(['headers', 'subject', 'body']));

            return view('error-correo', [
                'title' => 'Ocurrió un problema',
                'message' => 'No pudimos enviar tu solicitud en este momento. 
                      Por favor, intenta nuevamente en unos minutos 
                      o contáctanos por WhatsApp o teléfono.'
            ]);
        }
    }
}
