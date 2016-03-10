<?php
/**
 * F2S Client example
 *
 */
include 'vendor/autoload.php';

use GuzzleHttp\Client;

class F2SClient
{

    private $client;
    private $config = [];

    private $responses = [];

    public function __construct($config)
    {
        $this->config = $config;
        $this->client = new Client([
            'base_uri' => $config['host']
        ]);
    }

    public function verify($data = [])
    {
        return $this->batch([$data]);
    }

    public function batch($items = [])
    {
        $this->responses = [];
        try {
            $response = $this->client->request('GET', '/api/check', [
                'query' => array_map([$this, 'cleanData'], $items) + ['key' => $this->config['key']]
            ]);

            $result = true;
            $content = json_decode($response->getBody()->getContents(), true);
            foreach ($content as $k => $v) {
                if (!$v['valid']) {
                    $result = false;
                }
                $this->responses[$k] = $v;
            }
            return $result;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $this->d() && print($e->getMessage());
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->d() && print($e->getMessage());
        }

        return false;
    }

    /**
     * Make sure only valid parameters are sent
     *
     * @param array $data
     * @return array
     */
    public function cleanData($data)
    {
        return array_intersect_key(array_flip(['firstname', 'lastname', 'email', 'phone', 'address1', 'address2']), $data);
    }

    /**
     * Check if debug mode is enabled
     *
     * @return bool
     */
    private function d()
    {
        return isset($this->config['debug']) && $this->config['debug'];
    }
}

// --

$client = new F2SClient([
    'host' => 'http://localhost:8000',
    'key' => 'YOUR_KEY',
    'debug' => true
]);

$isValid = $client->verify([
    'firstname' => 'John',
    'lastname' => 'Doe',
    'email' => 'localhost@localhost'
]);

print("Check test #1\n");
var_dump($isValid);


$isValid = $client->batch([
    [
        'firstname' => 'John',
        'lastname' => 'Doe',
        'email' => 'localhost@localhost'
    ],
    [
        'firstname' => 'Jane',
        'lastname' => 'Doe',
        'email' => 'locallyhost@localhost'
    ],
]);

print("Check test #2\n");
var_dump($isValid);