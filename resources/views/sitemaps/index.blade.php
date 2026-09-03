<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @for ($page = 1; $page <= $eventPages; $page++)
        <sitemap>
            <loc>{{ route('sitemap.events', ['page' => $page]) }}</loc>
            @if ($eventLastModified)<lastmod>{{ $eventLastModified }}</lastmod>@endif
        </sitemap>
    @endfor
    @for ($page = 1; $page <= $photoPages; $page++)
        <sitemap>
            <loc>{{ route('sitemap.photos', ['page' => $page]) }}</loc>
            @if ($photoLastModified)<lastmod>{{ $photoLastModified }}</lastmod>@endif
        </sitemap>
    @endfor
</sitemapindex>
