<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    @foreach ($photos as $photo)
        <url>
            <loc>{{ route('events.photos.show', ['event' => $photo->event->slug, 'photo' => $photo]) }}</loc>
            <lastmod>{{ $photo->updated_at->toAtomString() }}</lastmod>
            <image:image>
                <image:loc>{{ route('events.photos.image', ['event' => $photo->event->slug, 'photo' => $photo]) }}</image:loc>
            </image:image>
        </url>
    @endforeach
</urlset>
