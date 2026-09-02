<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterPhotographerRequest;
use App\Mail\PhotographerApplicationReceived;
use App\Models\Photographer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/** Handles the existing public photographer application workflow. */
class PhotographerRegistrationController extends Controller
{
    /** Create a pending photographer account using the applicant's password. */
    public function store(RegisterPhotographerRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        [$user] = DB::transaction(function () use ($validated): array {
            $user = User::create([
                'name' => "{$validated['first_name']} {$validated['last_name']}",
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $photographerRole = Role::firstOrCreate(['name' => 'photographer']);
            $user->roles()->attach($photographerRole);

            $photographer = Photographer::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'city' => $validated['city'],
                'state' => strtoupper($validated['state']),
                'camera_model' => $validated['camera_model'],
                'profile_url' => $validated['profile_url'],
                'about' => $validated['about'],
                'age_confirmed_at' => now(),
                'terms_accepted_at' => now(),
            ]);

            return [$user, $photographer];
        });

        Auth::login($user);

        try {
            event(new Registered($user));
            Mail::to($user)->send(new PhotographerApplicationReceived($user));
        } catch (Throwable $exception) {
            Log::error('Photographer registration email delivery failed.', [
                'event' => 'photographers.registration.email',
                'user_id' => $user->id,
                'exception' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('verification.notice')
            ->with('success', 'Application received. Please verify your email address.');
    }
}
