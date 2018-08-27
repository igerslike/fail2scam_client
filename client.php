<?php

namespace F2S;

use GuzzleHttp\Client as GuzzleClient;

class Client
{

    /**
     * Validates against all data, regardless of dangerous level, non verified
     */
    const LEVEL_RISKY = 'RISKY';
    /**
     * Matches against verified data, and protect against high level reports
     */
    const LEVEL_STRICT = 'STRICT';

    /**
     * The location of Fail2Scam host
     */
    const F2S_HOST = 'http://fail2scam.com';

    private $client;
    private $config = [];

    public function __construct($config)
    {
        $this->config = $config;
        $this->client = new GuzzleClient([
            'base_uri' => self::F2S_HOST
        ]);
    }

    /**
     * Validate a single record
     *
     * @param Record $record
     * @param string $acceptanceLevel
     * @return Record
     * @throws Exception
     */
    public function single(Record $record, $acceptanceLevel = self::LEVEL_STRICT)
    {
        return $this->batch([$record], $acceptanceLevel)[0];
    }

    /**
     * Validate a batch of records
     *
     * @param Record[] $records
     * @param string $acceptanceLevel
     * @return array
     * @throws Exception
     */
    public function batch($records = [], $acceptanceLevel = self::LEVEL_STRICT)
    {
        try {
            $items = [];
            foreach ($records as $k => $record) {
                $items['record:' . $k] = $record->getData();
            }

            $query = [
                'data' => $items,
                'key' => $this->config['key'],
                'verbose' => isset($this->config['verbose'])?:false,
                'level' => $acceptanceLevel
            ];

            $response = $this->client->request('POST', '/api/check', [
                'form_params' => $query
            ]);

            $content = json_decode($response->getBody()->getContents(), true);
            if (isset($content['message'])) {
                throw new \Exception($content['message']);
            }
            foreach ($records as $k => $record) {
                $record->setValidity($content['valid']['record:' . $k]);
                if (!$record->isValid() && isset($this->config['verbose']) && $this->config['verbose']) {
                    $record->setMessages($content['reports']['messages']['record:' . $k]);
                }
            }
            return $records;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            throw new \Exception("Could not connect to host!");
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Report a record
     *
     * @param Record $record
     * @param null|string $reason
     * @return bool
     */
    public function report(Record $record, $reason = null)
    {
        null !== $reason && $record->setReason($reason);
        $query = array_merge([
            'key' => $this->config['key']
        ], $record->getData());
        $response = $this->client->request('POST', '/api/report', [
            'form_params' => $query
        ]);
        return $response->getStatusCode() === 201;
    }
}
