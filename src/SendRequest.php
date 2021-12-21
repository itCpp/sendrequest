<?php

namespace ItCpp\Sendrequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use ItCpp\Sendrequest\IncomingEvent;

class SendRequest
{
    /**
     * Версия пакета
     * 
     * @var string
     */
    const VERSION = "0.1.1";

    /**
     * Отправка запроса на сервер приёма
     * 
     * @param \Illuminate\Http\Request
     * @return array
     */
    public static function send(Request $request)
    {
        return IncomingEvent::done($request);
    }

    /**
     * Шифрование всех ключей массива
     * 
     * @param array|object $data
     * @return array
     */
    public static function encrypt($data)
    {
        if (!is_array($data) and !is_object($data))
            return Crypt::encryptString($data);

        $response = [];

        foreach ($data as $key => $row) {
            $response[$key] = (is_array($row) or is_object($row))
                ? self::encrypt($row)
                : Crypt::encryptString($row);
        }

        return $response;
    }
}
