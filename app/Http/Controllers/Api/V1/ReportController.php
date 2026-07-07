<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Mail\NewReportNotification;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class ReportController extends Controller
{
    /**
     * POST /reports — register a citizen emergency report and notify the on-duty
     * officer(s). Success is returned WITHOUT a `data` wrapper per the contract
     * (docs/API-CONTRACT.md §4).
     */
    public function store(StoreReportRequest $request): JsonResponse
    {
        $report = Report::create($request->safe()->only([
            'type', 'region', 'location', 'description', 'phone',
        ]));

        $this->notifyDutyOfficers($report);

        return response()->json([
            'ok' => true,
            'reference' => $report->reference,
        ]);
    }

    private function notifyDutyOfficers(Report $report): void
    {
        $recipients = collect(explode(',', (string) config('khf.duty.emails')))
            ->map(fn (string $email): string => trim($email))
            ->filter()
            ->values()
            ->all();

        if ($recipients === []) {
            return;
        }

        Mail::to($recipients)->queue(new NewReportNotification($report));
    }
}
