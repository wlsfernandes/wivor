<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @if ($includeSitePages)
        <url>
            <loc>{{ route('welcome') }}</loc>
        </url>
        <url>
            <loc>{{ route('events.listEvents') }}</loc>
        </url>
    @endif
    @foreach ($events as $event)
        <url>
            <loc>{{ route('events.show', ['event' => $event->slug]) }}</loc>
            <lastmod>{{ $event->updated_at->toAtomString() }}</lastmod>
        </url>
    @endforeach
</urlset>
