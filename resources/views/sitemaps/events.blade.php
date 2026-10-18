<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @if ($includeSitePages)
        @foreach (['welcome', 'events.listEvents', 'how-it-works', 'photographers', 'organizers', 'faq', 'about', 'contact_us'] as $sitePageRoute)
            <url>
                <loc>{{ route($sitePageRoute) }}</loc>
            </url>
        @endforeach
    @endif
    @foreach ($events as $event)
        <url>
            <loc>{{ route('events.show', ['event' => $event->slug]) }}</loc>
            <lastmod>{{ $event->updated_at->toAtomString() }}</lastmod>
        </url>
    @endforeach
</urlset>
