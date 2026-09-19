<?php

namespace App\Jobs;

use App\Models\PhotographerTransfer;
use App\Services\PhotographerTransferService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Processes one order-and-photographer Stripe transfer without overlapping retries. */
class ProcessPhotographerTransfer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public int $photographerTransferId) {}

    /** Return the retry delays for temporary Stripe or network failures. */
    public function backoff(): array
    {
        return [60, 300];
    }

    /** Prevent concurrent jobs from attempting the same photographer transfer. */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("photographer-transfer-{$this->photographerTransferId}"))
                ->dontRelease()
                ->expireAfter($this->timeout + 30),
        ];
    }

    /** Process the transfer unless an earlier attempt already succeeded. */
    public function handle(PhotographerTransferService $photographerTransferService): void
    {
        $photographerTransfer = PhotographerTransfer::find($this->photographerTransferId);

        if (! $photographerTransfer
            || $photographerTransfer->status === PhotographerTransfer::STATUS_SUCCEEDED) {
            return;
        }

        $photographerTransferService->processTransfer($photographerTransfer);
    }

    /** Keep an exhausted transfer visible as failed and log its local identity. */
    public function failed(Throwable $exception): void
    {
        $photographerTransfer = PhotographerTransfer::find($this->photographerTransferId);

        if ($photographerTransfer
            && $photographerTransfer->status !== PhotographerTransfer::STATUS_SUCCEEDED
            && blank($photographerTransfer->last_error)) {
            $photographerTransfer->update([
                'status' => PhotographerTransfer::STATUS_FAILED,
                'last_error' => 'Stripe transfer failed.',
            ]);
        }

        Log::error('Photographer transfer job exhausted its retries.', [
            'photographer_transfer_id' => $this->photographerTransferId,
            'order_id' => $photographerTransfer?->order_id,
            'photographer_id' => $photographerTransfer?->photographer_id,
            'exception_class' => $exception::class,
        ]);
    }
}
