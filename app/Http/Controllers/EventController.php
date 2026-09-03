<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Models\Photo;
use App\Models\Photographer;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

/**
 * Handles public Event discovery and authenticated Event management.
 *
 * The existing controller is retained so current route names and integrations
 * continue to work while the Event MVP fields are introduced.
 */
class EventController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    /** Display events the authenticated user may manage. */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Event::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:draft,published,archived'],
            'event_date' => ['nullable', 'date'],
        ]);
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');
        $query = $isAdmin ? Event::query() : $user->photographer->events();
        $events = $query
            ->search($filters['search'] ?? null)
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['event_date']), fn ($query) => $query->whereDate('date_of_event', $filters['event_date']))
            ->latest('date_of_event')
            ->paginate(20)
            ->withQueryString();

        return view('events.index', [
            'events' => $events,
            'filters' => $filters,
            'statusFilters' => [
                Event::STATUS_DRAFT => 'Draft',
                Event::STATUS_PUBLISHED => 'Published',
                Event::STATUS_ARCHIVED => 'Archived',
            ],
            'layout' => $isAdmin ? 'layouts.master' : 'layouts.app-sidebar',
        ]);
    }

    /** Display the searchable public event directory. */
    public function listEvents(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'size:2'],
            'city' => ['nullable', 'string', 'max:120'],
            'sport' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $events = Event::query()
            ->published()
            ->search($filters['search'] ?? null)
            ->when(isset($filters['state']), fn ($query) => $query->where('state', strtoupper($filters['state'])))
            ->when(isset($filters['city']), fn ($query) => $query->where('city', $filters['city']))
            ->when(isset($filters['sport']), fn ($query) => $query->where('sport', $filters['sport']))
            ->when(isset($filters['date_from']), fn ($query) => $query->whereDate('date_of_event', '>=', $filters['date_from']))
            ->when(isset($filters['date_to']), fn ($query) => $query->whereDate('date_of_event', '<=', $filters['date_to']))
            ->orderByDesc('date_of_event')
            ->paginate(12)
            ->withQueryString();

        $filterOptions = [
            'states' => Event::published()->whereNotNull('state')->distinct()->orderBy('state')->pluck('state'),
            'cities' => Event::published()->whereNotNull('city')->distinct()->orderBy('city')->pluck('city'),
            'sports' => Event::published()->whereNotNull('sport')->distinct()->orderBy('sport')->pluck('sport'),
        ];

        return view('events.list-events', [
            'events' => $events,
            'filters' => $filters,
            'filterOptions' => $filterOptions,
            'layout' => 'layouts.app',
        ]);
    }

    /** Display a published event by its public slug. */
    public function show(Request $request, Event $event): View
    {
        abort_unless($event->status === Event::STATUS_PUBLISHED, 404);

        $photosLiveLabel = $event->photos_live_at
            ? $event->photos_live_at->timezone($event->timezone)->format('F j, Y \a\t g:i A T')
            : null;

        $availabilityMessage = match (true) {
            $event->sales_close_at?->isPast() => 'This event gallery is now closed.',
            $event->photos()->where('status', Photo::STATUS_PUBLISHED)->exists() => 'Browse the photographs currently published for this event.',
            $event->public_availability_label === 'Photos coming soon' => "Photos for this event are not available yet. Please return after {$photosLiveLabel}.",
            default => 'Event photography is being prepared. Please check back soon.',
        };

        $photos = $event->photos()->with('photographer')->where('status', Photo::STATUS_PUBLISHED)
            ->when($event->sales_close_at?->isPast(), fn ($query) => $query->whereRaw('1 = 0'))
            ->latest('published_at')->paginate(48);

        $canonicalUrl = route('events.show', ['event' => $event->slug]);
        if ($request->integer('page') > 1) {
            $canonicalUrl .= '?page='.$request->integer('page');
        }

        $cartEvent = $this->cart->event();

        return view('events.post-show', [
            'event' => $event,
            'seoTitle' => "{$event->title} Photos | WivorPhotos",
            'seoDescription' => "Find professional photos from {$event->title} in {$event->location_label}.",
            'canonicalUrl' => $canonicalUrl,
            'availabilityMessage' => $availabilityMessage,
            'photos' => $photos,
            'cartPhotoUuids' => $cartEvent?->is($event) ? $this->cart->photos()->pluck('uuid') : collect(),
            'cartCount' => $this->cart->count(),
            'cartSubtotalCents' => $this->cart->subtotalCents(),
        ]);
    }

    /** Display the event creation form. */
    public function create(Request $request): View
    {
        Gate::authorize('create', Event::class);

        return view('events.create', $this->formViewData($request));
    }

    /** Store a new event and assign it to the creating photographer. */
    public function store(StoreEventRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $coverPath = $this->storeCover($request);

        unset($validated['image_url']);
        $validated = $this->normalizeEventTimes($validated);
        $validated['slug'] = Event::generateUniqueSlug($validated['title']);
        $validated['image_url'] = $coverPath;
        $validated['content'] ??= '';
        $validated['published'] = $validated['status'] === Event::STATUS_PUBLISHED;
        $validated['published_at'] = $validated['published'] ? now() : null;
        $validated['price_cents'] = (int) round(((float) $validated['price']) * 100);
        unset($validated['price']);

        try {
            $event = DB::transaction(function () use ($request, $validated): Event {
                $event = Event::create($validated);
                $photographer = $request->user()->photographer;

                if ($photographer) {
                    $event->photographers()->syncWithoutDetaching([$photographer->id]);
                }

                return $event;
            });
        } catch (Throwable $exception) {
            $this->deleteManagedCover($coverPath);
            Log::error('Event creation failed.', ['event' => 'events.store', 'exception' => $exception->getMessage()]);

            return back()->withInput()->withErrors(['error' => 'The event could not be created. Please try again.']);
        }

        return redirect()->route('events.edit', ['event' => $event->id])->with('success', 'Event created successfully.');
    }

    /** Display the event edit form for an authorized manager. */
    public function edit(Request $request, Event $event): View
    {
        Gate::authorize('update', $event);

        $viewData = $this->formViewData($request, $event);

        if ($request->user()->hasRole('admin')) {
            $viewData['approvedPhotographers'] = Photographer::query()
                ->where('status', Photographer::STATUS_APPROVED)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();
            $event->load('photographers');
        }

        return view('events.edit', $viewData);
    }

    /** Update an event and safely replace its optional cover image. */
    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $validated = $request->validated();
        $newCoverPath = $this->storeCover($request);
        $oldCoverPath = $event->image_url;

        unset($validated['image_url']);
        $validated = $this->normalizeEventTimes($validated);
        $validated['content'] ??= '';
        $validated['published'] = $validated['status'] === Event::STATUS_PUBLISHED;
        $validated['price_cents'] = (int) round(((float) $validated['price']) * 100);
        unset($validated['price']);

        if ($validated['published'] && ! $event->published_at) {
            $validated['published_at'] = now();
        } elseif (! $validated['published']) {
            $validated['published_at'] = null;
        }

        if ($newCoverPath) {
            $validated['image_url'] = $newCoverPath;
        }

        try {
            DB::transaction(fn () => $event->update($validated));
        } catch (Throwable $exception) {
            $this->deleteManagedCover($newCoverPath);
            Log::error('Event update failed.', [
                'event' => 'events.update',
                'event_id' => $event->id,
                'exception' => $exception->getMessage(),
            ]);

            return back()->withInput()->withErrors(['error' => 'The event could not be updated. Please try again.']);
        }

        if ($newCoverPath && $oldCoverPath !== $newCoverPath) {
            $this->deleteManagedCover($oldCoverPath);
        }

        return redirect()->route('events.index')->with('success', 'Event updated successfully.');
    }

    /** Toggle an event between draft and published. */
    public function publish(Event $event): RedirectResponse
    {
        Gate::authorize('update', $event);

        $publish = $event->status !== Event::STATUS_PUBLISHED;

        DB::transaction(function () use ($event, $publish): void {
            $event->update([
                'status' => $publish ? Event::STATUS_PUBLISHED : Event::STATUS_DRAFT,
                'published' => $publish,
                'published_at' => $publish ? ($event->published_at ?? now()) : null,
            ]);
        });

        return back()->with('success', $publish ? 'Event published successfully.' : 'Event unpublished successfully.');
    }

    /** Assign an approved photographer to an event with upload access. */
    public function assignPhotographer(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'photographer_id' => ['required', 'integer', 'exists:photographers,id'],
        ]);

        $photographer = Photographer::query()
            ->whereKey($validated['photographer_id'])
            ->where('status', Photographer::STATUS_APPROVED)
            ->first();

        if (! $photographer) {
            return back()->withErrors(['photographer_id' => 'Only approved photographers can be assigned.']);
        }

        $event->photographers()->syncWithoutDetaching([
            $photographer->id => ['status' => 'approved'],
        ]);

        return back()->with('success', "{$photographer->full_name} was assigned and approved for this event.");
    }

    /** Archive an event instead of permanently deleting it. */
    public function destroy(Event $event): RedirectResponse
    {
        Gate::authorize('delete', $event);

        DB::transaction(fn () => $event->update([
            'status' => Event::STATUS_ARCHIVED,
            'published' => false,
            'published_at' => null,
        ]));

        return redirect()->route('events.index')->with('success', 'Event archived successfully.');
    }

    /** Return shared form data prepared for direct display. */
    private function formViewData(Request $request, ?Event $event = null): array
    {
        $eventTimezone = $event?->timezone ?? 'America/New_York';

        return [
            'event' => $event,
            'layout' => $request->user()->hasRole('admin') ? 'layouts.master' : 'layouts.app-sidebar',
            'statuses' => [
                Event::STATUS_DRAFT => 'Draft — only managers can see this event',
                Event::STATUS_PUBLISHED => 'Published — the public event page is visible',
                Event::STATUS_ARCHIVED => 'Archived — hidden from the public directory',
            ],
            'timezones' => [
                'America/New_York' => 'Eastern Time',
                'America/Chicago' => 'Central Time',
                'America/Denver' => 'Mountain Time',
                'America/Los_Angeles' => 'Pacific Time',
                'America/Anchorage' => 'Alaska Time',
                'Pacific/Honolulu' => 'Hawaii Time',
            ],
            'formValues' => [
                'title' => $event?->title ?? '',
                'sport' => $event?->sport ?? '',
                'content' => $event?->content ?? '',
                'summary' => $event?->summary ?? '',
                'date_of_event' => $event?->date_of_event?->format('Y-m-d') ?? '',
                'starts_at' => $event?->starts_at?->timezone($eventTimezone)->format('Y-m-d\TH:i') ?? '',
                'ends_at' => $event?->ends_at?->timezone($eventTimezone)->format('Y-m-d\TH:i') ?? '',
                'photos_live_at' => $event?->photos_live_at?->timezone($eventTimezone)->format('Y-m-d\TH:i') ?? '',
                'timezone' => $eventTimezone,
                'venue_name' => $event?->venue_name ?? '',
                'city' => $event?->city ?? '',
                'state' => $event?->state ?? '',
                'price' => $event?->price_cents ? number_format($event->price_cents / 100, 2, '.', '') : '',
                'status' => $event?->status ?? Event::STATUS_DRAFT,
                'cover_url' => $event?->cover_url,
            ],
        ];
    }

    /** Store a validated cover and return its disk-relative path. */
    private function storeCover(Request $request): ?string
    {
        return $request->hasFile('image_url')
            ? $request->file('image_url')->store('events/covers', config('filesystems.event_covers'))
            : null;
    }

    /** Delete only covers managed by the configured Event cover disk. */
    private function deleteManagedCover(?string $path): void
    {
        if ($path && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk(config('filesystems.event_covers'))->delete($path);
        }
    }

    /** Convert event-local date-times to the application storage timezone. */
    private function normalizeEventTimes(array $validated): array
    {
        foreach (['starts_at', 'ends_at', 'photos_live_at'] as $field) {
            if (! empty($validated[$field])) {
                $validated[$field] = Carbon::parse($validated[$field], $validated['timezone'])->utc();
            }
        }

        return $validated;
    }
}
