<?php

namespace App\Services;

interface AdmissionService
{
    function getEnrolment($request);
    function getSchoolVisit($request);
    function getObservation($request);

    function getAcademicYear($request);
    function getBranch($request);
    function getLevelByBranch($branchId, $request);
    function getGradeByLevel($levelId, $request);
}
