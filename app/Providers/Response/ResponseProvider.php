<?php

namespace App\Providers\Response;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\MessageInterface;

use App\Providers\Slim\SlimProvider;

class ResponseProvider
{
    /**
     * данные для ответа 
     */
    private mixed $data;

    /**
     * символьный код ответа 
     */
    private string $code = '';

    /**
     * Текстовая информация ошибки ответа 
     */
    private string $message = '';

    /**
     * статус ответа 
     */
    private int $status = 200;

    /**
     * заголовки ответа
     */
    private array $headers = [];


    /**
     * Поиск в массиве по частичному совпадению значений или по выбранному типу сравнения
     *
     * @param  array|string|bool|int|Closure  $data - тело запроса, массив данных
     * @param  int $status - статус запроса
     * @param  array  $headers - заголовки
     * @return $this
     */

    public function __construct(mixed $data = null, int $status = 0, array $headers = [])
    {
        $this->data = $data;
        if ($status > 0) $this->status($status);
        if ($headers) $this->headers($headers);
    }


    /**
     * Устанавливаем статус успешного json ответа 200
     *
     * @return $this
     */

    public function success(): MessageInterface
    {
        return $this->status(200)->get();
    }

    /**
     * Устанавливаем статус не успешного json ответа 403
     *
     * @return $this
     */

    public function setMessage(mixed $message): self
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Устанавливаем статус не успешного json ответа 403
     *
     * @return $this
     */

    public function error(): MessageInterface
    {
        return $this->status(403)->get();
    }

    /**
     * Установка json заголовков
     *
     * @return $this
     */

    public function json(): self
    {
        $this->headers['Content-type'] = 'application/json; charset=utf-8';
        $this->type = 'json';

        return $this;
    }

    /**
     * Установка stream заголовков
     *
     * @return $this
     */

    public function stream(): self
    {
        $this->headers['Content-type'] = 'text/event-stream; charset=utf-8';
        $this->type = 'stream';

        return $this;
    }

    /**
     * Устанавливаем статус json ответа
     *
     * @param  int $status - статус запроса
     * @param  array  $headers - заголовки
     * @return $this
     */

    public function status($status = 200): self
    {
        switch ($status) {
            case 100:
                $this->code = 'Continue';
                break;
            case 101:
                $this->code = 'Switching Protocols';
                break;
            case 200:
                $this->code = 'OK';
                break;
            case 201:
                $this->code = 'Created';
                break;
            case 202:
                $this->code = 'Accepted';
                break;
            case 203:
                $this->code = 'Non-Authoritative Information';
                break;
            case 204:
                $this->code = 'No Content';
                break;
            case 205:
                $this->code = 'Reset Content';
                break;
            case 206:
                $this->code = 'Partial Content';
                break;
            case 300:
                $this->code = 'Multiple Choices';
                break;
            case 301:
                $this->code = 'Moved Permanently';
                break;
            case 302:
                $this->code = 'Moved Temporarily';
                break;
            case 303:
                $this->code = 'See Other';
                break;
            case 304:
                $this->code = 'Not Modified';
                break;
            case 305:
                $this->code = 'Use Proxy';
                break;
            case 400:
                $this->code = 'Bad Request';
                break;
            case 401:
                $this->code = 'Unauthorized';
                break;
            case 402:
                $this->code = 'Payment Required';
                break;
            case 403:
                $this->code = 'Forbidden';
                break;
            case 404:
                $this->code = 'Not Found';
                break;
            case 405:
                $this->code = 'Method Not Allowed';
                break;
            case 406:
                $this->code = 'Not Acceptable';
                break;
            case 407:
                $this->code = 'Proxy Authentication Required';
                break;
            case 408:
                $this->code = 'Request Time-out';
                break;
            case 409:
                $this->code = 'Conflict';
                break;
            case 410:
                $this->code = 'Gone';
                break;
            case 411:
                $this->code = 'Length Required';
                break;
            case 412:
                $this->code = 'Precondition Failed';
                break;
            case 413:
                $this->code = 'Request Entity Too Large';
                break;
            case 414:
                $this->code = 'Request-URI Too Large';
                break;
            case 415:
                $this->code = 'Unsupported Media Type';
                break;
            case 500:
                $this->code = 'Internal Server Error';
                break;
            case 501:
                $this->code = 'Not Implemented';
                break;
            case 502:
                $this->code = 'Bad Gateway';
                break;
            case 503:
                $this->code = 'Service Unavailable';
                break;
            case 504:
                $this->code = 'Gateway Time-out';
                break;
            case 505:
                $this->code = 'HTTP Version not supported';
                break;
            default:
                $this->code = 'Bad Gateway';
                break;
        }

        $this->status = $status;

        return $this;
    }

    public function headers(array $headers = []): self
    {
        foreach ($headers as $k => $v)
            $this->headers[$k] = $v;

        return $this;
    }

    public function get(): MessageInterface
    {
        $body = [
            'error' => $this->status !== 200 ? $this->status : 0,
            'message' => $this->message,
            'data' => $this->data
        ];

        $response = SlimProvider::getApp()->getResponseFactory()->createResponse();
        $response->getBody()->write(json_encode($body));

        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
