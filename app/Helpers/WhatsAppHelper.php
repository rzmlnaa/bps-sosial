<?php

namespace App\Helpers;

class WhatsAppHelper
{
    public static function kirimPesanWhatsApp($phone, $message)
    {
        $token = "5Tzht4GEmSJJa4KFnFE7PhaIwqCbjkMrbqYf8lzGteQE9YX3a4";

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://wanesia.com/api/send_message',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query([
                'token' => $token,
                'number' => $phone,
                'message' => $message,
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ],
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

}
