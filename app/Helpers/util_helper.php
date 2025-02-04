<?php


if (!function_exists('signkeys')) {
    function signkeys($array_params, $secretKey)
    {

        // order keys
        $keys = array_keys($array_params);
        sort($keys);

        //concatenation of keys
        $toSign = "";
        foreach ($keys as $key) {
            $toSign .= $key . $array_params[$key];
        };

        //signing
        $signature = hash_hmac('sha256', $toSign, $secretKey);
       
        return  [
            'signature' => $signature,
            'keys' => array_keys($keys)
        ];
    }
}
