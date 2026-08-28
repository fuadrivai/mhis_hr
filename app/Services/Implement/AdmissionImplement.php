<?php

namespace App\Services\Implement;

use App\Services\AdmissionService;
use Illuminate\Support\Facades\Http;

class AdmissionImplement implements AdmissionService
{
    protected $token;
    protected $url;

    public function __construct()
    {
        $this->url = config('services.admission.url');
        $this->token = config('services.admission.service_token');
    }

    public function getEnrolment($request)
    {
        $url = $this->url . '/enrolment';
        $length = max((int) $request->input('length', 10), 1);
        $query = $request->except(['draw', 'columns', 'order', 'start', 'length', 'search']);
        $query['perpage'] = $length;
        $query['page'] = (int) floor((int) $request->input('start', 0) / $length) + 1;
        if ($request->filled('search.value')) {
            $query['search'] = $request->input('search.value');
        }
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get($url, $query);
        return $response;
    }
    public function getAcademicYear($request)
    {
        return $this->get('/academic-year', $request->query());
    }

    public function getBranch($request)
    {
        return $this->get('/branch', $request->query());
    }

    public function getLevelByBranch($branchId, $request)
    {
        return $this->get('/level/branch/' . $branchId, $request->query());
    }

    public function getGradeByLevel($levelId, $request)
    {
        return $this->get('/grade/level/' . $levelId, $request->query());
    }

    private function get($path, array $query = [])
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get($this->url . $path, $query);
    }
    public function getSchoolVisit($request)
    {}
    public function getObservation($request)
    {}
}
