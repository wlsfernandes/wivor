<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Event;
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
        return view('events.list-events', compact('events'));
    }
    public function show($slug)
    {
        // Find the event by slug, ensuring it is published
       $event= Event::where('slug', $slug)->where('published', true)->firstOrFail();

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
           $event= Event::find($id);
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
           $event= Event::findOrFail($id);
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
           $event= Event::findOrFail($id);
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
           $event= Event::findOrFail($id);
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
           $event= Event::findOrFail($id);
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
        try {
            DB::beginTransaction();
    
            $slug = Str::slug($request->input('title'));
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
    

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
    
            $event = Event::findOrFail($id);
            $event->update($request->all());
            $event->event_type_id = $request->input('event_type');
    
            // Handle image upload if present
            if ($request->hasFile('image_url')) {
                $imageUrl = FileUploader::uploadImageToS3Storage($request->file('image_url'));
                $event->image_url = $imageUrl;
            }
            $event->save();
            DB::commit();
    
            session()->flash('success', 'Event updated successfully.');
            Log::info('Event updated successfully with uploads.');
            return redirect()->route('event.index');
    
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating event: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update Event: ' . $e->getMessage()]);
        }
    }
    

}
