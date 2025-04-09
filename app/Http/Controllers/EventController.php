<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Event;
use App\Models\Photographer;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Helpers\FileUploader;
use Exception;



class EventController extends Controller
{
    public function index()
    {
        $events = Event::all();
        return view('events.index', compact('events'));
    }
    public function listEvents()
    {
        $events = Event::where('published', true)
            ->orderBy('published_at', 'desc')
            ->paginate(6);
        $layout = 'layouts.app';
        return view('events.list-events', compact('events', 'layout'));
    }
    public function show($slug)
    {
        // Find the event by slug, ensuring it is published
        $event = Event::where('slug', $slug)->where('published', true)->firstOrFail();

        // Return the view with the event data
        return view('events.post-show', compact('event'));
    }



    public function showAllEvents()
    {
        $events = Event::where('published', true)
            ->orderBy('published_at', 'desc')
            ->paginate(3);

        return view('pages.events', compact('events'));
    }

    public function create()
    {
        return view('events.create');
    }
    public function edit(string $id): View
    {
        try {
            $event = Event::find($id);
            return view('events.edit', compact('event'));
        } catch (Exception $e) {
            Log::error('Error fetching event details: ' . $e->getMessage());
            session()->now('error', 'An error occurred while fetching event details.');
            return redirect()->back();
        }
    }
    public function teaser(string $id): View
    {
        try {
            $event = Event::findOrFail($id);
            return view('events.teaser', compact('event'));
        } catch (Exception $e) {
            Log::error('Error fetching digitalCollections: ' . $e->getMessage());
            session()->now('error', 'An error occurred while fetching digitalCollections.');
            return redirect()->back();
        }
    }


    public function uploadJPG(Request $request, $id)
    {

        try {
            DB::beginTransaction();
            $event = Event::findOrFail($id);
            // Store the file in Amazon S3
            $file = $request->file('document');
            if ($request->hasFile('image')) {
                $url = FileUploader::uploadImageToS3Storage($request->file('image')); // Call the helper
                $event->image_url = $url;
                $event->save();
                DB::commit();
                session()->flash('success', 'File uploaded successfully.');
                Log::info('File uploaded successfully.');
                return redirect()->route('events.index');
            } else {
                DB::rollBack();
                $error = $file->getError();
                return back()->withErrors(['document' => 'File upload error: ' . $error]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to upload file: ' . $e->getMessage());
            Log::error('Error uploading file: ' . $e->getMessage());
            return redirect()->route('events.index');
        }
    }


    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();
            $event = Event::findOrFail($id);
            $event->delete();
            DB::commit();
            session()->flash('success', 'Event deleted successfully.');
            Log::info('Event deleted successfully.');
            return redirect()->route('events.index');
        } catch (Exception $e) {
            DB::rollBack();
            session()->now('error', 'deleting event: ' . $e->getMessage());
            Log::error('Error deleting event: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to delete event: ']);
        }
    }

    public function publish(string $id)
    {
        try {
            DB::beginTransaction();
            $event = Event::findOrFail($id);
            if (!$event->image_url) {
                return redirect()->route('events.index')->withErrors([
                    'error' => 'To publish a blog, you must upload a JPG image.'
                ]);
            }
            $event->published = !$event->published;
            $event->save();
            DB::commit();
            session()->flash('success', 'Event Published successfully.');
            Log::info('Event Published successfully.');
            return redirect()->route('events.index');
        } catch (Exception $e) {
            DB::rollBack();
            session()->now('error', 'publishing event: ' . $e->getMessage());
            Log::error('Error publishing event: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to delete event: ']);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'date_of_event' => 'required|date',
            'file_url' => 'required|file',
        ]);

        try {
            DB::beginTransaction();

            $slug = Event::generateUniqueSlug($request->input('title'));
            $event = new Event();
            $event->fill($request->all());
            $event->slug = $slug;
            $event->published_at = now();
            $event->save();

            if (!$request->hasFile('image_url')) {
                DB::rollBack();
                return back()->withErrors(['image' => 'Image file is required.']);
            }

            $imageUrl = FileUploader::uploadImageToS3Storage($request->file('image_url'));
            $event->image_url = $imageUrl;
            $event->save();

            DB::commit();

            session()->flash('success', 'Event created and image uploaded successfully.');
            Log::info('Event created successfully with image.');
            return redirect()->route('events.index');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating Event: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to create event: ' . $e->getMessage()]);
        }
    }

    public function eventCreatedByPhotographer(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'date_of_event' => 'required|date',
            'image_url' => 'required|file',
        ]);

        $photographer = Photographer::where('user_id', Auth::id())->first();
        if (!$photographer) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Photographer profile not found.']);
        }
        try {
            DB::beginTransaction();
            $slug = Event::generateUniqueSlug($request->input('title'));
            $event = new Event();
            $event->fill($request->all());
            $event->slug = $slug;
            $event->published_at = now();
            $event->published = true;
            $event->save();

            if (!$request->hasFile('image_url')) {
                DB::rollBack();
                return back()->withErrors(['image' => 'Image file is required.']);
            }

            $imageUrl = FileUploader::uploadImageToS3Storage($request->file('image_url'));
            $event->image_url = $imageUrl;
            $event->save();
            //vinculate photographer to event
            $photographer->events()->attach($event->id);
            DB::commit();
            session()->flash('success', 'Event created and image uploaded successfully.');
            Log::info('Event created successfully with image.');
            return redirect()->route('photographer.newEvent');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating Event: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to create event:']);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $event = Event::findOrFail($id);
            if ($request->input('title') !== $event->title) {
                $event->slug = Event::generateUniqueSlug($request->input('title'));
            }
            // Update fillable fields
            $event->fill($request->only([
                'title',
                'published',
                'published_at',
                'date_of_event',
                'file_url',
                'content',
                'summary',
            ]));
            if ($request->hasFile('image_url')) {
                $imageUrl = FileUploader::uploadImageToS3Storage($request->file('image_url'));
                $event->image_url = $imageUrl;
            }
            $event->save();
            DB::commit();
            session()->flash('success', 'Event updated successfully.');
            Log::info('Event updated successfully with uploads.');
            return redirect()->route('events.index');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating event: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors([
                'error' => 'Failed to update Event: ' . $e->getMessage()
            ]);
        }
    }



}
