<?php

namespace Shreejalmaharjan27\PhpFakeyou;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;

class FakeYou {
    protected string $endpoint = "https://api.fakeyou.com";
    protected ?string $authCookie = null;

    /**
     * Create a Request to FakeYou API
     *
     * @param string $endpoint
     * @param array|null $data
     * @param string $method
     * @param boolean $rawResponse If you want the Guzzle Response Object
     * @param string $dataType json|multipart
     *
     * @return array|ResponseInterface
     */
    public function request(
        string $endpoint,
        array $data = null, 
        string $method = "GET",
        bool $rawResponse = false,
        string $dataType = 'json'
    ): array|ResponseInterface {
        $client = new Client([
            'base_uri'=>$this->endpoint,
            'cookies'=>true
        ]);

        $options = [
            'headers'=>[]
        ];


        if (isset($data) && $dataType === 'json') {
            $options['headers']['Content-Type'] = 'application/json';
            $options['json'] = $data;
        }
        
        if (isset($data) && $dataType == 'multipart') {
            $multipartData = [];
            foreach ($data as $key => $value) {
                $multipartData[] = [
                    'name' => $key,
                    'contents' => $value,
                ];
            }
            $options['multipart'] = $multipartData;
        }

        if(isset($this->authCookie)) {
            $options['headers']['Cookie'] = $this->authCookie;
        }
        $request = new Request($method, $endpoint);
        $response = $client->send($request, $options);
        if ($rawResponse) {
            return $response;
        }

        return json_decode($response->getBody()->getContents(), true);

    }

    /**
     * Login to FakeYou
     *
     * @param string $email
     * @param string $password
     *
     * @return void
     */
    public function login(string $email, string $password): void
    {
        $data = [
            'username_or_email'=>$email,
            'password'=>$password
        ];

        $data = $this->request('/login', $data, 'POST', true);
        $cookie = $data->getHeader('Set-Cookie');
        $cookie = strtok($cookie[0], ';');
        $this->authCookie = $cookie;
    }

    /**
     * Geneate an Audio from Text
     *
     * @param string $message The message to be converted to audio
     * @param string $model The model to be used for the audio
     *
     * @return array
     */
    public function audio(string $message, string $model): array
    {
        $data = [
            'uuid_idempotency_token'=>Uuid::uuid4()->toString(),
            'inference_text'=>$message,
            'tts_model_token'=>$model,
        ];

        return $this->request('/tts/inference', $data, 'POST');
    }

    /**
     * Check if the audio/video is ready
     *
     * @param string $token
     * @param string $type
     *
     * @return array
     */
    public function check(string $token, string $type): array 
    {
        switch($type) {
            case 'tts': 
            case 'audio':
                $url = sprintf('/tts/job/%s', $token);
                break;

            case 'w2l':
            case 'lipsync':
                $url = sprintf('/w2l/job/%s', $token);
                break;
            
            default: 
                throw new Exception('Invalid type');
        }
        return $this->request($url);
    }

    /**
     * Generate a LipSync Video
     *
     * @param string $audio Audio URL; Can either be a URL or a path to a file
     *
     * @return array
     */
    public function lipsync(string $audio, string $model): array
    {
        $getAudio = file_get_contents($audio);

        $payload = [
            'audio'=>$getAudio,
            'template_token' => $model,
            'uuid_idempotency_token' => Uuid::uuid4()->toString(),
        ];
        $response = $this->request('/w2l/inference', $payload, 'POST', false, 'multipart');
        return $response;
    }

    /**
     * Get a list of all Audio Voices
     *
     * @return array
     */
    public function audioList(): array
    {
        return $this->request('/tts/list');
    }

    /**
     * Get a list of all Lipsync Models
     *
     * @return array
     */
    public function lipsyncList(): array
    {
        return $this->request('/w2l/list');
    }
}