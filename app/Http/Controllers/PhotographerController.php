<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\TemporaryPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\View\View;
use App\Models\Photographer;
use Exception;

class PhotographerController extends Controller
{

    public function photographers(Request $request)
    {
        return view('photographers.page');
    }
    public function list(): View
    {
        try {
            $photographers = Photographer::all();
            return view('photographers.index', compact('photographers'));
        } catch (Exception $e) {
            Log::error('Error fetching photographers: ' . $e->getMessage());
            session()->now('error', 'An error occurred while fetching photographers.');
            return redirect()->back();
        }
    }

    protected function registerPhotographer(Request $request)
    {

        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
        ];

        // Define custom messages
        $messages = [
            'email.unique' => 'This email is already registered. Please login.',
        ];

        // Manually create the validator instance
        $validatedData = Validator::make($request->all(), $rules, $messages);

        // Check if validation fails
        if ($validatedData->fails()) {
            // If email already exists, return custom flash message
            if ($validatedData->errors()->has('email')) {
                return redirect()->back()->with('error', 'This email is already registered. Please login.')->withInput();
            }

            // Otherwise, return the default validation errors
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        DB::beginTransaction();

        try {

            // Generate a temporary password
            $temporaryPassword = Str::random(10);

            // Create user with a hashed temporary password
            $user = User::create([
                'name' => $request->input('first_name', null) . ' ' . $request->input('last_name', null),
                'email' => $request->input('email', null),
                'password' => Hash::make($temporaryPassword), // Store hashed password
            ]);

            // Assign Photographer role
            if (method_exists($user, 'roles')) {
                $user->roles()->attach(2); // Ensure role ID exists in your roles table
            } else {
                throw new Exception('User roles relationship not found.');
            }

            // Create Photographer profile
            Photographer::create([
                'user_id' => $user->id,
                'first_name' => $request->input('first_name', null),
                'last_name' => $request->input('last_name', null),
                'phone' => $request->input('phone', null),
                'address' => $request->input('address', null),
                'city' => $request->input('city', null),
                'state' => $request->input('state', null),
                'zipcode' => $request->input('zipcode', null),
                'camera_model' => $request->input('camera_model', null),
                'profile_url' => $request->input('profile_url', null),
                'about' => $request->input('about', null),
            ]);

            // Send email with temporary password
            Mail::to($user->email)->send(new TemporaryPasswordMail($user, $temporaryPassword));

            DB::commit();

            // Flash success message to session and redirect
            return redirect()->back()->with('success', 'Registration successful! Temporary password sent via email.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating user: ' . $e->getMessage());

            // Flash error message to session and redirect
            return redirect()->back()->with('error', 'Registration failed. Please try again.');
        }
    }


   


}
