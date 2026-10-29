<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmContact;
use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Browse the CRM contact directory and each contact's participation and consent history.
 */
class CrmContactController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
        ]);
        $search = isset($filters['search']) ? mb_strtolower(trim($filters['search'])) : null;
        $eventId = $filters['event_id'] ?? null;

        $contacts = CrmContact::query()
            ->when($search, fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $pattern = '%'.addcslashes($search, '%_\\').'%';
                $query->where('email_normalized', 'like', $pattern)
                    ->orWhereRaw('lower(first_name) like ?', [$pattern])
                    ->orWhereRaw('lower(last_name) like ?', [$pattern])
                    ->orWhereRaw('lower(full_name) like ?', [$pattern]);
            }))
            ->when($eventId, fn (Builder $query) => $query->whereHas('events', fn (Builder $query) => $query->whereKey($eventId)))
            ->withCount('events')
            ->orderBy('email_normalized')
            ->paginate(50)
            ->withQueryString();

        return view('admin.crm.contacts.index', [
            'contacts' => $contacts,
            'events' => Event::orderByDesc('date_of_event')->orderBy('title')->get(['id', 'title']),
            'filters' => ['search' => $filters['search'] ?? '', 'event_id' => $eventId],
        ]);
    }

    public function show(CrmContact $contact): View
    {
        return view('admin.crm.contacts.show', [
            'contact' => $contact->load([
                'events' => fn ($query) => $query->orderByDesc('date_of_event'),
                'consents.scopedEvent:id,title',
                'suppressions',
            ]),
        ]);
    }
}
