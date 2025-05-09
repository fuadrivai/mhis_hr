<?php

namespace App\Services\Implement;

use App\Services\PersonService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;

use function App\Helpers\getUserTalentaByEmail;
use function App\Helpers\talentaHeader;

class PersonImplement implements PersonService
{
    function get($request)
    {
        $data = $request->except('user');

        $query = http_build_query($data);
        $client =  new Client([
            'base_uri' => $_ENV['MEKARI_API_BASE_URL']
        ]);

        $method     = 'GET';
        $path       = '/v2/talenta/v3/employees';
        $queryParam = "?" . $query;
        $headers    = ['X-Idempotency-Key' => '1234'];

        try {
            $response = $client->request(
                $method,
                $path . $queryParam,
                ['headers'   => array_merge(talentaHeader($method, $path . $queryParam), $headers)]
            );

            $res = json_decode($response->getBody());

            return $res->data;
        } catch (ClientException $e) {
            echo Psr7\Message::toString($e->getRequest());
            echo Psr7\Message::toString($e->getResponse());
            return response()->json(PHP_EOL);
        }
    }
    function show($id)
    {
        $client =  new Client([
            'base_uri' => $_ENV['MEKARI_API_BASE_URL']
        ]);

        $method     = 'GET';
        $path       = '/v2/talenta/v2/employee/' . $id;
        $queryParam = "";
        $headers    = ['X-Idempotency-Key' => '1234'];

        try {
            $response = $client->request(
                $method,
                $path . $queryParam,
                ['headers'   => array_merge(talentaHeader($method, $path . $queryParam), $headers)]
            );

            $res = json_decode($response->getBody());

            return $res->data->employee;
        } catch (ClientException $e) {
            echo Psr7\Message::toString($e->getRequest());
            echo Psr7\Message::toString($e->getResponse());
            return response()->json(PHP_EOL);
        }
    }
    function showByemail($email)
    {
        return getUserTalentaByEmail($email);
    }
    function getPersonalData($companyId, $request)
    {

        $client =  new Client([
            'base_uri' => $_ENV['MEKARI_API_BASE_URL']
        ]);

        $method     = 'GET';
        $path       = "/v2/talenta/v2/company/" . $companyId . "/personal";
        $queryParam = "?user_id=" . $request['user_id'];
        $headers    = ['X-Idempotency-Key' => '1234'];

        try {
            $response = $client->request(
                $method,
                $path . $queryParam,
                ['headers'   => array_merge(talentaHeader($method, $path . $queryParam), $headers)]
            );

            $res = json_decode($response->getBody());

            return $res;
        } catch (ClientException $e) {
            echo Psr7\Message::toString($e->getRequest());
            echo Psr7\Message::toString($e->getResponse());
            return response()->json(PHP_EOL);
        }
    }
    function post($request) {}
    function put($request) {}
    function delete($id) {}
}
