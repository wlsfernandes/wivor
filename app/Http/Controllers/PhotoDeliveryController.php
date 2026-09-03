<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Photo;
use App\Services\PhotographerUploadAccess;
use App\Services\PhotoStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PhotoDeliveryController extends Controller
{
    public function photographerPreview(Request $request, Event $event, Photo $photo, PhotographerUploadAccess $access, PhotoStorage $storage): RedirectResponse
    {
        $access->assignment($request->user(), $event);
        abort_unless($photo->event_id === $event->id && $photo->photographer_id === $request->user()->photographer->id, 404);
        abort_unless($photo->thumbnail_key && in_array($photo->status, [Photo::STATUS_READY, Photo::STATUS_PUBLISHED], true), 404);
        return redirect()->away($storage->deliveryUrl($photo->thumbnail_key));
    }

    public function gallery(Event $event, Photo $photo, PhotoStorage $storage): RedirectResponse
    {
        abort_unless($event->status === Event::STATUS_PUBLISHED && $photo->event_id === $event->id && $photo->status === Photo::STATUS_PUBLISHED && $photo->preview_key, 404);
        abort_if($event->sales_close_at?->isPast(), 404);
        return redirect()->away($storage->deliveryUrl($photo->preview_key));
    }
}
