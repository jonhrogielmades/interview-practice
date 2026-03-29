<?php

namespace App\View\Components\interview;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InterviewMetrics extends Component
{
    public function __construct()
    {
        //
    }

    public function render(): View|Closure|string
    {
        return view('components.interview.interview-metrics');
    }
}
