<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    public const TYPE_GOVERNMENT = 'GOVERNMENT';
    public const TYPE_COMPANY = 'COMPANY';
    public const TYPE_SCHOOL = 'SCHOOL';

    public const CATEGORY_REGULAR = 'REGULAR';
    public const CATEGORY_NEW_ACADEMIC_YEAR = 'NEW_ACADEMIC_YEAR';

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];

    public static function types(): array
    {
        return [
            self::TYPE_GOVERNMENT,
            self::TYPE_COMPANY,
            self::TYPE_SCHOOL,
        ];
    }

    public static function categories(): array
    {
        return [
            self::CATEGORY_REGULAR,
            self::CATEGORY_NEW_ACADEMIC_YEAR,
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
