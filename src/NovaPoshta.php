<?php

namespace Daaner\NovaPoshta;

use Daaner\NovaPoshta\Contracts\NovaPoshtaInterface;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NovaPoshta implements NovaPoshtaInterface
{
    protected $baseUri;
    protected $point;

    protected $api;
    protected $url;
    protected $dev;

    /**
     * NovaPoshta constructor main settings.
     */
    public function __construct()
    {
        $this->baseUri = config('novaposhta.base_uri');
        $this->point = config('novaposhta.point');
        $this->dev = config('novaposhta.dev');
        $this->getApi();
        $this->url = $this->baseUri.$this->point;
    }

    /**
     * @return string
     */
    public function getApi(): string
    {
        if (! $this->api) {
            $this->api = config('novaposhta.api_key');
        }

        return $this->api;
    }

    /**
     * Устанавливаем другой API ключ.
     *
     * @param  string  $api
     */
    public function setApi(string $api): void
    {
        $this->api = $api;
    }

    /**
     * @param  string  $model
     * @param  string  $calledMethod
     * @param  array|null  $methodProperties
     * @param  bool  $auth
     * @return array
     */
    public function getResponse(string $model, string $calledMethod, ?array $methodProperties, bool $auth = true): array
    {
        $url = $this->url.'/'.$model.'/'.$calledMethod;
        $body = [];
        $info = '';

        $body['modelName'] = $model;
        $body['calledMethod'] = $calledMethod;
        $body['methodProperties'] = $methodProperties;

        if ($auth) {
            $body['apiKey'] = $this->api;
        }

        $response = Http::timeout(config('novaposhta.http_response_timeout', 3))
            ->retry(config('novaposhta.http_retry_max_time', 2), config('novaposhta.http_retry_delay', 200))
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post($url, $body);

        if ($response->failed()) {
            return [
                'success' => false,
                'result' => null,
                'info' => trans('novaposhta::novaposhta.error_data'),
            ];
        }

        $answer = $response->json();
        if ($auth === false && isset($answer[0])) {
            /**
             * костыль для Новой Почты. Спасибо Вам большое :).
             */
            $answer = $answer[0];
        }

        if (! isset($answer['success']) || ! isset($answer['data']) || empty($answer['data'])) {
            /**
             * Что-то не так в ответе.
             */
            $info = trans('novaposhta::novaposhta.error_answer');
            $success = false;
            $result = null;
        } else {
            $success = $answer['success'];
            $result = $answer['data'];
        }

        /**
         * Ошибки, либо уведомления.
         */
        $info = [];
        if (isset($answer['warnings']) && $answer['warnings']) {
            $info['warnings'] = $answer['warnings'];

            if ($answer['errors']) {
                $info['errors'] = $answer['errors'];
                if ($answer['errorCodes']) {
                    foreach ($answer['errorCodes'] as $err) {
                        $info['StatusCode'] = $err;
                        $info['StatusLocale'] = __('novaposhta::novaposhta.statusCode.'.$err);
                    }
                }
            }
        }

        if (! $info && isset($answer['info']) && $answer['info']) {
            $info['info'] = $answer['info'];
        }

        $return = [
            'success' => $success,
            'result' => $result,
            'info' => $info,
        ];

        if ($this->dev) {
            /**
             * Test and Dev.
             */
            Log::debug('= = = = = = = = = = = = = = = = = = = =');
            Log::debug($model.' / '.$calledMethod.' // apiKey: '.$auth);
            Log::debug('--------------------');

            try {
                Log::notice(json_encode($methodProperties));
            } catch (Exception $e) {
                Log::notice('method json_encode error');
            }

            $return['dev'] = $answer;
        }

        return $return;
    }
}
