<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CandidateImport implements ToArray, WithHeadingRow
{
    public function array(array $array): array
    {
        return $array;
    }

    public static function templateHeadings(): array
    {
        return [
            'Full Name',
            'Email',
            'Phone Number',
            'Current City',
            'Position Applied',
            'Branch Preference',
            'Application Source',
            'Source Detail',
            'Consent',
        ];
    }
}
