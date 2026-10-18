<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SitemapController extends Controller
{
    private const PAGE_SIZE = 10_000;

    public function index(): Response
    {
        $eventPages = max(1, (int) ceil(Event::published()->count() / self::PAGE_SIZE));
        $eventUpdatedAt = Event::published()->max('updated_at');

        return $this->xml('sitemaps.index', [
            'eventPages' => $eventPages,
            'eventLastModified' => $eventUpdatedAt ? Carbon::parse($eventUpdatedAt)->toAtomString() : null,
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

    private function xml(string $view, array $data): Response
    {
        return response()
            ->view($view, $data)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
