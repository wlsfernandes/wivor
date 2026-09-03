<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\MediaActivityLog;
use App\Models\Photo;
use App\Notifications\EventMediaExpirationWarning;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class EnforceMediaRetention extends Command
{
    protected $signature = 'media:enforce-retention {--warnings-only}';
    protected $description = 'Send gallery expiration warnings and remove eligible expired media.';

    public function handle(): int
    {
        $this->sendWarnings();
        if (! $this->option('warnings-only')) {
            $this->cleanRejectedAndAbandonedUploads();
            $this->deleteExpiredMedia();
        }

        return self::SUCCESS;
    }

    private function cleanRejectedAndAbandonedUploads(): void
    {
        Photo::where('status', Photo::STATUS_REJECTED)->whereNull('deleted_at')
            ->where('processed_at', '<=', now()->subDay())->chunkById(100, function ($photos): void {
                foreach ($photos as $photo) {
                    try {
                        Storage::disk(config('photo_uploads.disk'))->delete(array_filter([$photo->original_key, $photo->preview_key, $photo->thumbnail_key]));
                        $photo->update(['deleted_at' => now(), 'deletion_reason' => 'rejected_upload']);
                        MediaActivityLog::create(['event_id' => $photo->event_id, 'photo_id' => $photo->id, 'action' => 'rejected_media_deleted']);
                    } catch (Throwable $exception) {
                        Log::error('Rejected media deletion failed.', ['photo_id' => $photo->id, 'exception' => $exception->getMessage()]);
                        MediaActivityLog::create(['event_id' => $photo->event_id, 'photo_id' => $photo->id, 'action' => 'deletion_failed']);
                    }
                }
            });

        Photo::whereIn('status', [Photo::STATUS_QUEUED, Photo::STATUS_UPLOADING])
            ->where('created_at', '<=', now()->subDays(2))->chunkById(100, function ($photos): void {
                foreach ($photos as $photo) {
                    try {
                        Storage::disk(config('photo_uploads.disk'))->delete($photo->original_key);
                        $photo->update(['status' => Photo::STATUS_REMOVED, 'deleted_at' => now(), 'deletion_reason' => 'abandoned_upload']);
                        MediaActivityLog::create(['event_id' => $photo->event_id, 'photo_id' => $photo->id, 'action' => 'abandoned_upload_deleted']);
                        $photo->batch->recalculate();
                    } catch (Throwable $exception) {
                        Log::error('Abandoned upload deletion failed.', ['photo_id' => $photo->id, 'exception' => $exception->getMessage()]);
                        MediaActivityLog::create(['event_id' => $photo->event_id, 'photo_id' => $photo->id, 'action' => 'deletion_failed']);
                    }
                }
            });
    }

    private function sendWarnings(): void
    {
        Event::whereNotNull('sales_close_at')->where('sales_close_at', '>', now())->chunkById(100, function ($events): void {
            foreach ($events as $event) {
                $days = now()->diffInDays($event->sales_close_at, false);
                $field = match (true) {
                    $days <= 3 && ! $event->retention_warning_3_sent_at => 'retention_warning_3_sent_at',
                    $days <= 14 && ! $event->retention_warning_14_sent_at => 'retention_warning_14_sent_at',
                    default => null,
                };
                if (! $field) {
                    continue;
                }

                $warningDays = $field === 'retention_warning_3_sent_at' ? 3 : 14;
                $event->photographers()->with('user')->get()->each(
                    fn ($photographer) => $photographer->user?->notify(new EventMediaExpirationWarning($event, $warningDays))
                );
                $event->update([$field => now()]);
            }
        });
    }

    private function deleteExpiredMedia(): void
    {
        Photo::where('status', Photo::STATUS_PUBLISHED)->where('expires_at', '<=', now())
            ->chunkById(100, function ($photos): void {
                foreach ($photos as $photo) {
                    if ($photo->hasActiveHold()) {
                        continue;
                    }

                    $protectedUntil = $photo->event->sales_close_at;
                    if ($photo->sale_count > 0 && ! $photo->most_recent_purchase_at) {
                        MediaActivityLog::create(['event_id' => $photo->event_id, 'photo_id' => $photo->id, 'action' => 'retention_blocked_missing_purchase_time']);
                        continue;
                    }
                    if ($photo->sale_count > 0 && $photo->most_recent_purchase_at) {
                        $purchaseProtection = $photo->most_recent_purchase_at->copy()->addDays(config('photo_uploads.sold_original_days'));
                        if (! $protectedUntil || $purchaseProtection->isAfter($protectedUntil)) {
                            $protectedUntil = $purchaseProtection;
                        }
                    }
                    if ($protectedUntil?->isFuture()) {
                        try {
                            // The closed gallery no longer needs display assets; retain only the protected sold original.
                            Storage::disk(config('photo_uploads.disk'))->delete(array_filter([$photo->preview_key, $photo->thumbnail_key]));
                            $photo->preview_key = null;
                            $photo->thumbnail_key = null;
                            $photo->update(['expires_at' => $protectedUntil]);
                            MediaActivityLog::create(['event_id' => $photo->event_id, 'photo_id' => $photo->id, 'action' => 'sold_derivatives_deleted']);
                        } catch (Throwable $exception) {
                            Log::error('Scheduled derivative deletion failed.', ['photo_id' => $photo->id, 'exception' => $exception->getMessage()]);
                            MediaActivityLog::create(['event_id' => $photo->event_id, 'photo_id' => $photo->id, 'action' => 'deletion_failed']);
                        }
                        continue;
                    }

                    try {
                        $deleted = Storage::disk(config('photo_uploads.disk'))->delete(array_filter([
                            $photo->original_key, $photo->preview_key, $photo->thumbnail_key,
                        ]));
                        if (! $deleted) {
                            throw new \RuntimeException('Storage reported an unsuccessful deletion.');
                        }
                        $photo->update([
                            'status' => Photo::STATUS_REMOVED,
                            'deleted_at' => now(),
                            'deletion_reason' => $photo->sale_count > 0 ? 'sold_original_protection_expired' : 'unsold_gallery_expired',
                        ]);
                        MediaActivityLog::create(['event_id' => $photo->event_id, 'photo_id' => $photo->id, 'action' => 'retention_deleted']);
                    } catch (Throwable $exception) {
                        Log::error('Scheduled media deletion failed.', ['photo_id' => $photo->id, 'exception' => $exception->getMessage()]);
                        MediaActivityLog::create(['event_id' => $photo->event_id, 'photo_id' => $photo->id, 'action' => 'deletion_failed']);
                    }
                }
            });
    }
}
