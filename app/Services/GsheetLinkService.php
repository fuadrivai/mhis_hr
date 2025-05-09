<?php

namespace App\Services;

interface GsheetLinkService
{

    function getSchoolCalendar();
    function getNewsletter();
    function getKPI($request);
    function getGeneralAnnouncement();
}
