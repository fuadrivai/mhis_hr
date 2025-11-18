<?php

namespace App\Services\Implement;

use App\Models\Employee;
use App\Models\InternalDocument;
use App\Services\InternalDocumentService;
use GuzzleHttp\Client;

class InternalDocumentImplement implements InternalDocumentService
{
    function get()
    {
    }
    function show($id) {}
    function post($request)
    {

        $employee = Employee::with(['employment','personal'])
            ->where('user_id', auth()->id())->first();

        $payload = [
            "apiKey"=>"mhisapproval",
            "requesterEmail"=>$employee->personal->email,
            "documentTitle"=>$request['title'],
            "documentLink"=>$request['link'],
            "requesterName"=>$employee->personal->fullname,
            "branch"=>$employee->employment->branch_name,
            "division"=>$employee->employment->organization_name,
            "position"=>$employee->employment->job_position_name,
            "type"=>$request['type'],
            "notes"=>$request['notes']
        ];

        try {
            $client =  new Client([
                'base_uri' => "https://script.google.com"
            ]);
            $path = "/macros/s/AKfycbyRkNjrJW0inuTghIBAUr00J7jHY3yKvvLguE-HzwYKEaMqsOqIWdtGygciX4bwz4rsgg/exec";
            $response = $client->request(
                "POST",
                $path,
                [
                    'headers'   => [
                        "Content-Type" => "application/json"
                    ],
                    'json' => $payload,
                    'timeout' => 15,
                ]
            );
            $result = json_decode($response->getBody(),true);

            if (isset($result['ok']) && $result['ok'] === true) {

                $document = new InternalDocument();
                $document->user_id = auth()->id();
                $document->employee_id = $employee->id;
                $document->code = $result['requestId'] ?? null;
                $document->title = $request['title'];
                $document->link = $request['link'];
                $document->notes = $request['notes'];
                $document->type = $request['type'];
                $document->save();

                return response()->json([
                    'message' => 'Document submitted successfully',
                    'data' => $document
                ], 201);
            }

            return response()->json($result);
        } catch (\Throwable $th) {
            $code = $th->getCode();
            if ($code < 100 || $code > 599) {
                $code = 500;
            }

            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
            ], $code);
        }
    }
    function put($id, $request)
    {
    }
    function delete($id) {}
    function getByUserId($id)
    {
         

    }
}
