<?php

namespace ItCpp\Sendrequest;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Utils;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ItCpp\Sendrequest\Models\FailedSend;

class IncomingEvent extends SendRequest
{
    /**
     * Обработка отправки заявки
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public static function done(Request $request)
    {
        $data = $request->all(); # Входящие данные

        // Адрес сайта
        $parse = parse_url($request->server('HTTP_ORIGIN'));
        $data['site'] = $parse['host'] ?? $request->server('HTTP_ORIGIN');

        // Преобразование доменного имени из IDNA ASCII в Unicode
        $idn_to_utf8 = idn_to_utf8($data['site']);

        if ($data['site'] != $idn_to_utf8)
            $data['site'] = $idn_to_utf8;

        // Преборазование строки
        if (!empty($data['_search'])) {
            $data['_search'] = (new SearchString($data['_search']))->getArray();
        }

        if ($request->server('QUERY_STRING')) {
            $data['_query_string'] = (new SearchString($request->server('QUERY_STRING')))->getArray();
        }

        $data['_host'] = $request->server('HTTP_HOST'); # Хост для отладки

        if ($request->server('HTTP_REFERER'))
            $data['_referer'] = $request->server('HTTP_REFERER'); # Реферальная ссылка

        // Заголовки для запроса
        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => env("APP_NAME", "Sender Text Request") . "/" . parent::VERSION . " (" . env('APP_URL') . ") " . Utils::defaultUserAgent(),
            'X-User-Agent' => $request->header('User-Agent'),
            'X-Remote-Addr' => $request->ip(),
        ];

        // Массив для записи в таблицу при неудачной отправке
        $request_data = [
            'body' => $data,
            'headers' => $headers,
        ];

        $fail = null; # Ошибочное выполнение запроса

        // Глобальный ответ
        $response = [
            'message' => "Заявка принята",
        ];

        try {

            $client = new Client([
                'base_uri' => env('EVENT_HANDLING_URL', 'http://localhost:8000/'),
                'verify' => false,
                'headers' => $headers,
                'form_params' => $data,
                'connect_timeout' => 10,
            ]);

            $client_respones = $client->request('POST', 'inText');

            $response = json_decode($client_respones->getBody());
        }
        // Исключение при отсутсвии соединения с сервером-приемки
        catch (ConnectException $e) {
            $fail = [
                'request_count' => 1,
                'response_code' => 0,
                'response_data' => [
                    'message' => $e->getMessage(),
                ],
            ];
        }
        // Исключение при ошибочном ответе от сервера-приемки
        catch (ClientException $e) {
            $fail = [
                'request_count' => 1,
                'response_code' => $e->getCode(),
                'response_data' => json_decode($e->getResponse()->getBody())
            ];
        } catch (Exception $e) {
            $fail = [
                'request_code' => $e->getCode(),
                'response_data' => $e->getMessage(),
            ];
        }

        if ($fail) {
            $fail['request_data'] = array_merge(
                $request_data,
                ['body' => parent::encrypt($request_data['body'])],
            );
            $fail['fail_at'] = date("Y-m-d H:i:s");

            $response['error'] = self::fail($fail);

            if ($request->showFailed)
                $response['fail'] = $fail;
        }

        return new JsonResponse($response);
    }

    /**
     * Запись ошибочного запроса в БД
     * 
     * @param array $data
     * @return string
     */
    public static function fail($data)
    {
        try {
            FailedSend::create($data); # Запись ошибочного запроса в БД
        }
        // Исключение при наличии ошибки в БД
        catch (QueryException $e) {

            self::writeToFile($data); # Запись информации в файл

            return is_array($e->errorInfo) ? implode(" ", $e->errorInfo) : "Ошибка базы данных";
        }

        return "Запрос отложен";
    }

    /**
     * Запись информации в файл
     * 
     * @param array $data
     * @return null
     */
    public static function writeToFile($data)
    {
        $storage_file = storage_path("app/faileds.json");
        $response = [];

        if (file_exists($storage_file))
            $response = json_decode(file_get_contents($storage_file), true);

        $response[] = $data;

        file_put_contents($storage_file, json_encode($response));

        return null;
    }
}
