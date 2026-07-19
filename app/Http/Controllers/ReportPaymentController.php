<?php

namespace App\Http\Controllers;

use App\Jobs\NotifyReportWebhook;
use App\Models\Report;
use App\Payments\StreamPayClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ReportPaymentController extends Controller
{
    public function checkout(Report $report, StreamPayClient $streamPay): RedirectResponse
    {
        abort_if($report->html_content === null, 404, 'This report has not finished generating yet.');

        if ($report->isUnlocked()) {
            return redirect()->route('reports.show', $report);
        }

        $link = $streamPay->createPaymentLink(
            name: "CloudPilot AI Report #{$report->id} - Full Unlock",
            successUrl: route('reports.payment.callback', $report),
            failureUrl: route('reports.payment.callback', $report),
            metadata: ['report_id' => (string) $report->id],
        );

        $report->update(['stream_payment_link_id' => $link['id']]);

        return redirect($link['url']);
    }

    public function callback(Request $request, Report $report, StreamPayClient $streamPay): RedirectResponse
    {
        $invoiceId = $request->query('invoice_id');

        if ($invoiceId && ! $report->isUnlocked()) {
            $invoice = $streamPay->getInvoice($invoiceId);

            $this->unlockIfValid($report, $invoice);
        }

        return redirect()->route('reports.show', $report);
    }

    public function webhook(Request $request, StreamPayClient $streamPay): Response
    {
        if (! $streamPay->verifyWebhookSignature($request->getContent(), $request->header('X-Webhook-Signature', ''))) {
            Log::warning('StreamPay webhook signature verification failed.');

            return response('invalid signature', 401);
        }

        $payload = $request->json()->all();

        if (! in_array($payload['event_type'] ?? null, ['PAYMENT_SUCCEEDED', 'INVOICE_COMPLETED'], true)) {
            return response('ignored', 200);
        }

        $invoice = $payload['data']['invoice'] ?? null;
        $paymentLinkId = $invoice['payment_link_id'] ?? ($payload['data']['payment_link']['id'] ?? null);

        if (! $paymentLinkId) {
            return response('no payment_link_id in payload', 200);
        }

        $report = Report::where('stream_payment_link_id', $paymentLinkId)->first();

        if (! $report || $report->isUnlocked()) {
            return response('ok', 200);
        }

        $this->unlockIfValid($report, $invoice ?? $streamPay->getInvoice($payload['entity_id']));

        return response('ok', 200);
    }

    private function unlockIfValid(Report $report, array $invoice): void
    {
        if ($invoice['status'] !== 'COMPLETED') {
            return;
        }

        if (($invoice['payment_link_id'] ?? null) !== $report->stream_payment_link_id) {
            Log::warning('StreamPay invoice payment_link_id does not match report.', ['report_id' => $report->id]);

            return;
        }

        if (($invoice['currency'] ?? null) !== config('services.streampay.currency', 'SAR')) {
            Log::warning('StreamPay invoice currency mismatch.', ['report_id' => $report->id]);

            return;
        }

        $report->update([
            'unlocked_at' => now(),
            'stream_invoice_id' => $invoice['id'],
        ]);

        NotifyReportWebhook::dispatch($report->assessment);
    }
}
