<?php

namespace App\Providers;

use App\Services\ApprovalRuleService;
use App\Services\Implement\ApprovalRuleImplement;
use Illuminate\Support\ServiceProvider;

class ApprovalRuleProvider extends ServiceProvider
{
    public array $singletons = [
        ApprovalRuleService::class => ApprovalRuleImplement::class,
    ];

    public function register()
    {
        //
    }

    public function boot()
    {
        //
    }
}
