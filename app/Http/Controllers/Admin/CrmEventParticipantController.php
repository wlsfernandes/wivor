<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmContact;
use App\Models\Event;
use Illuminate\View\View;

/**
 * List the CRM participants linked to one event.
 */
class CrmEventParticipantController extends Controller
{
    public function index(Event $event): View
    {
        $participants = CrmContact::query()
            ->join('crm_event_contacts', 'crm_event_contacts.crm_contact_id', '=', 'crm_contacts.id')
            ->where('crm_event_contacts.event_id', $event->id)
            ->orderBy('crm_event_contacts.bib_number')
            ->orderBy('crm_contacts.email_normalized')
            ->select([
                'crm_contacts.*',
                'crm_event_contacts.bib_number',
                'crm_event_contacts.division',
            ])
            ->paginate(50);

        return view('admin.crm.events.participants', [
            'event' => $event,
            'participants' => $participants,
        ]);
    }
}
