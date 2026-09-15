<?php

namespace App\Jobs;

use App\Models\ReportingPeriod;
use App\Services\RulesEngineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EvaluateSurveillanceRules implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ReportingPeriod $period)
    {
    }

    public function handle(RulesEngineService $engine): void
    {
        $engine->evaluatePeriod($this->period);
    }
}
