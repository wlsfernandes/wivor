<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhotoRemovalRequest;
use App\Services\PhotoStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Handles administrator review of customer photo removal requests. */
class PhotoRemovalRequestController extends Controller
{
    public function __construct(private readonly PhotoStorage $storage)
    {
    }

    /** List removal requests, newest first, with a thumbnail for identification. */
    public function index(): View
    {
        $requests = PhotoRemovalRequest::with(['photo.event'])->latest()->paginate(30);

        $thumbnailUrls = $requests->getCollection()->mapWithKeys(fn (PhotoRemovalRequest $removalRequest) => [
            $removalRequest->id => $removalRequest->photo?->thumbnail_key ? $this->storage->deliveryUrl($removalRequest->photo->thumbnail_key) : null,
        ]);

        return view('admin.removal-requests.index', [
            'requests' => $requests,
            'thumbnailUrls' => $thumbnailUrls,
        ]);
    }

    /** Mark a removal request as reviewed. */
    public function resolve(Request $request, PhotoRemovalRequest $removalRequest): RedirectResponse
    {
        $removalRequest->update([
            'status' => PhotoRemovalRequest::STATUS_REVIEWED,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Removal request marked reviewed.');
    }
}
