<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Post;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Helpers\FileUploader;
use App\Models\PostType;
use Exception;



class PostController extends Controller
{
    public function index()
    {
        /*  $posts = Post::where('published', true)
              ->whereHas('postType', function ($query) {
                  $query->where('name', 'blog');
              })
              ->orderBy('published_at', 'desc')
              ->paginate(3); */
        $posts = Post::all();
        return view('posts.index', compact('posts'));
    }

    public function show($slug)
    {
        // Find the post by slug, ensuring it is published
        $post = Post::where('slug', $slug)->where('published', true)->firstOrFail();

        // Return the view with the post data
        return view('pages.post_show', compact('post'));
    }



    public function showAllEvents()
    {
        $posts = Post::where('published', true)
            ->whereHas('postType', function ($query) {
                $query->where('name', 'event');
            })
            ->orderBy('published_at', 'desc')
            ->paginate(3);

        return view('pages.events', compact('posts'));
    }

    public function create(): View
    {
        try {
            $post = new Post();
            $postTypes = PostType::all();
            return view('posts.create', compact('postTypes'));
        } catch (Exception $e) {
            Log::error('Error creating post: ' . $e->getMessage());
            session()->now('error', 'An error occurred while creating post.');
            return redirect()->back();
        }
    }
    public function edit(string $id): View
    {
        try {
            $post = Post::find($id);
            $postTypes = PostType::all();
            return view('posts.edit', compact('post', 'postTypes'));
        } catch (Exception $e) {
            Log::error('Error fetching post details: ' . $e->getMessage());
            session()->now('error', 'An error occurred while fetching post details.');
            return redirect()->back();
        }
    }
    public function teaser(string $id): View
    {
        try {
            $post = Post::findOrFail($id);
            return view('posts.teaser', compact('post'));
        } catch (Exception $e) {
            Log::error('Error fetching digitalCollections: ' . $e->getMessage());
            session()->now('error', 'An error occurred while fetching digitalCollections.');
            return redirect()->back();
        }
    }
    public function file(string $id): View
    {
        try {
            $post = Post::findOrFail($id);
            $language = request('language') === 'en' ? 'en' : 'es';
            return view('posts.file', compact('post', 'language'));
        } catch (Exception $e) {
            Log::error('Error fetching Post: ' . $e->getMessage());
            session()->now('error', 'An error occurred while fetching Post.');
            return redirect()->back();
        }
    }

    public function uploadJPG(Request $request, $id)
    {

        try {
            DB::beginTransaction();
            $post = Post::findOrFail($id);
            // Store the file in Amazon S3
            $file = $request->file('document');
            if ($request->hasFile('image')) {
                $url = FileUploader::uploadImageToS3Storage($request->file('image')); // Call the helper
                $post->image_url = $url;
                $post->save();
                DB::commit();
                session()->flash('success', 'File uploaded successfully.');
                Log::info('File uploaded successfully.');
                return redirect()->route('posts.index');
            } else {
                DB::rollBack();
                $error = $file->getError();
                return back()->withErrors(['document' => 'File upload error: ' . $error]);
            }
        } catch (Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to upload file: ' . $e->getMessage());
            Log::error('Error uploading file: ' . $e->getMessage());
            return redirect()->route('posts.index');
        }
    }

    public function uploadFile(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $post = Post::findOrFail($id);

            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $url = FileUploader::uploadPDFToS3Storage($file); // Upload to S3
                $language = request('language');

                if ($language === 'en') {
                    $post->file_url = $url;
                } else {
                    $post->file_url_es = $url;
                    $post->file_url_ptBR = $url;
                }
                $post->save();

                DB::commit();

                session()->flash('success', 'File uploaded successfully.');
                Log::info('File uploaded successfully for Post ID: ' . $id);

                return redirect()->route('posts.index');
            }

            return back()->withErrors(['document' => 'No file was uploaded.']);
        } catch (Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to upload file: ' . $e->getMessage());
            Log::error('Error uploading file for Post ID: ' . $id . '. Error: ' . $e->getMessage());

            return back();
        }
    }

    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();
            $post = Post::findOrFail($id);
            $post->delete();
            DB::commit();
            session()->flash('success', 'Post deleted successfully.');
            Log::info('Post deleted successfully.');
            return redirect()->route('posts.index');
        } catch (Exception $e) {
            DB::rollBack();
            session()->now('error', 'deleting post: ' . $e->getMessage());
            Log::error('Error deleting post: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to delete post: ']);
        }
    }

    public function publish(string $id)
    {
        try {
            DB::beginTransaction();
            $post = Post::findOrFail($id);
            if (!$post->image_url) {
                return redirect()->route('posts.index')->withErrors([
                    'error' => 'To publish a blog, you must upload a JPG image.'
                ]);
            }
            $post->published = !$post->published;
            $post->save();
            DB::commit();
            session()->flash('success', 'Post Published successfully.');
            Log::info('Post Published successfully.');
            return redirect()->route('posts.index');
        } catch (Exception $e) {
            DB::rollBack();
            session()->now('error', 'publishing post: ' . $e->getMessage());
            Log::error('Error publishing post: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to delete post: ']);
        }
    }

    // TODO PostType
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $slug = Str::slug($request->input('title_es'));
            $post = new Post($request->all());
            $post->post_type_id = $request->input('post_type');
            $post->slug = $slug;
            $post->published_at = now();
            $post->save();
            DB::commit();
            session()->flash('success', 'Post included successfully.'); // Flashing success message
            Log::info('Post included successfully.');
            return redirect()->route('posts.index');
        } catch (Exception $e) {
            DB::rollBack();
            session()->now('error', 'Failed to include Post: ' . $e->getMessage());
            Log::error('Error saving Post: ' . $e->getMessage());
            // return redirect()->back()->withInput()->withErrors(['error' => 'Failed to include post: ']);
            return redirect()->back()->withInput()->withErrors($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $post = Post::findOrFail($id);
            $post->update($request->all());
            $post->post_type_id = $request->input('post_type');
            $post->save();
            DB::commit();
            session()->flash('success', 'Post updated successfully.');
            Log::info('Post updated successfully.');
            return redirect()->route('post.index');
        } catch (Exception $e) {
            DB::rollBack();
            session()->now('error', 'Failed to update Post: ' . $e->getMessage());
            Log::error('Error saving post: ' . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update Post: ']);
        }
    }
}
