<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SitemapController extends Controller
{
    private const PAGE_SIZE = 10_000;

    public function index(): Response
    {
        $photoCount = $this->publicPhotos()->count();
        $eventPages = max(1, (int) ceil(Event::published()->count() / self::PAGE_SIZE));
        $photoPages = $photoCount > 0 ? (int) ceil($photoCount / self::PAGE_SIZE) : 0;
        $eventUpdatedAt = Event::published()->max('updated_at');
        $photoUpdatedAt = $this->publicPhotos()->max('updated_at');

        return $this->xml('sitemaps.index', [
            'eventPages' => $eventPages,
            'photoPages' => $photoPages,
            'eventLastModified' => $eventUpdatedAt ? Carbon::parse($eventUpdatedAt)->toAtomString() : null,
            'photoLastModified' => $photoUpdatedAt ? Carbon::parse($photoUpdatedAt)->toAtomString() : null,
        ]);
    }

    public function events(int $page): Response
    {
        abort_if($page < 1, 404);
        $events = Event::published()->orderBy('id')->forPage($page, self::PAGE_SIZE)->get();
        abort_if($events->isEmpty() && $page > 1, 404);

        return $this->xml('sitemaps.events', [
            'events' => $events,
            'includeSitePages' => $page === 1,
        ]);
    }

    public function photos(int $page): Response
    {
        abort_if($page < 1, 404);

        $photos = $this->publicPhotos()
            ->with('event')
            ->forPage($page, self::PAGE_SIZE)
            ->get();

        abort_if($photos->isEmpty(), 404);

        return $this->xml('sitemaps.photos', compact('photos'));
    }

    private function publicPhotos(): Builder
    {
        return Photo::query()
            ->where('status', Photo::STATUS_PUBLISHED)
            ->whereNotNull('preview_key')
            ->whereHas('event', fn (Builder $query): Builder => $query
                ->published()
                ->where(fn (Builder $query): Builder => $query
                    ->whereNull('sales_close_at')
                    ->orWhere('sales_close_at', '>', now())))
            ->orderBy('id');
    }

    private function xml(string $view, array $data): Response
    {
        return response()
            ->view($view, $data)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
