<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    public function show(Report $report): Response
    {
        abort_if($report->html_content === null, 404, 'This report has not finished generating yet.');

        return response($report->html_content)->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
