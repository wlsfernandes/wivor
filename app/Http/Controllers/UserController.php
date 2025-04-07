<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Mail\TemporaryPasswordMail;
use App\Models\User;
use App\Models\Customer;
use App\Models\Role;
use App\Models\AdvisorTeam;
use App\Models\Institution;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Exception;

class UserController extends Controller
{


    public function profile(): View
    {
        try {
            $user = auth()->user();
            return view('users.profile', compact('user'));
        } catch (Exception $e) {
            Log::error('Error fetching user details: ' . $e->getMessage());
            session()->now('error', 'An error occurred while fetching user details.');
            return redirect()->back();
        }
    }



    public function index(): View
    {
        try {
            $users = User::all();
            return view('users.index', compact('users'));
        } catch (Exception $e) {
            Log::error('Error fetching users: ' . $e->getMessage());
            session()->now('error', 'An error occurred while fetching users.');
            return redirect()->back();
        }
    }


    public function create(): View
    {
        try {
            $roles = Role::all();
            return view('users.create', compact('roles'));
        } catch (Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            session()->now('error', 'An error occurred while creating user.');
            return redirect()->back();
        }
    }

    public function destroy(string $id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            session()->flash('success', 'User deleted successfully.');
            Log::info('User deleted successfully: ', ['user_id' => $id]);

            return redirect()->back();

        } catch (Exception $e) {
            session()->flash('error', 'Failed to delete user: ' . $e->getMessage());
            Log::error('Error deleting user: ' . $e->getMessage());

            return redirect()->back()->withErrors(['error' => 'Failed to delete user.']);
        }
    }

    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                // Exclude 'role_ids' from mass assignment
                $user = new User($request->except('role_ids'));

                // Generate and hash a random password
                $plainPassword = Str::random(8);
                $user->password = Hash::make('adminWivor');
                $user->save();

                $roleIds = $request->input('role_ids'); // This could be a single value or an array
                $roleIds = is_array($roleIds) ? $roleIds : [$roleIds];
                if (!empty($roleIds)) {
                    $user->roles()->syncWithoutDetaching($roleIds);
                }

                /* Prepare email content
                $emailContent = "
                <h1>Hello, {$user->name}</h1>
                <p>Your account on Wivor was created. Here are your new login details:</p>
                <p><strong>Email:</strong> {$user->email}</p>
                <p><strong>Password:</strong> {$plainPassword}</p>
                <p>Please use these credentials to log in to your account. It is strongly recommended that you change your password after your first login.</p>
                <p>If you did not request this account creation, please contact our support team immediately.</p>
                <p>Best regards,<br>AETH</p>";

                // Send email (handled outside the transaction to prevent rollback issues)
                Mail::html($emailContent, function ($message) use ($user) {
                    $message->to($user->email);
                    $message->subject('Your new account and password');
                });
*/
                // Log success message
                Log::info('User created successfully: ', ['user_id' => $user->id, 'email' => $user->email]);

                session()->flash('success', 'User created successfully, and an email was sent with the login information.');
            });

            return redirect()->route('users.index');

        } catch (Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            session()->flash('error', 'Failed to create User: ' . $e->getMessage());

            return redirect()->back()->withInput()->withErrors(['error' => 'User creation failed. Please try again.']);
        }
    }




    public function edit(string $id): View
    {
        try {
            $user = User::find($id);
            $roles = Role::all();
            return view('users.edit', compact('user', 'roles'));
        } catch (Exception $e) {
            Log::error('Error fetching user details: ' . $e->getMessage());
            session()->now('error', 'An error occurred while fetching user details.');
            return redirect()->back();
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Validate input


            DB::beginTransaction();

            // Fetch the user by ID
            $user = User::findOrFail($id);

            // Update user information except for roles
            $user->update($request->except('role_ids'));

            // Sync the roles (many-to-many relationship)
            $roleIds = $request->input('role_ids'); // Get the array of role IDs from the request
            if ($roleIds) {
                $user->roles()->sync($roleIds); // Sync the user's roles
            }

            DB::commit();

            // Return success message
            return redirect()->back()->with('success', 'User updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            // Log the error and return an error message
            Log::error('Error updating user details: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while updating user details.');
        }
    }

    public function updatePassword(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'old_password' => 'required',
                'new_password' => 'required|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $user = User::find($id);

            if (!Hash::check($request->old_password, $user->password)) {
                return redirect()->back()->withErrors(['old_password' => 'The provided old password does not match our records.']);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return redirect()->back()->with('success', 'Password updated successfully.');
        } catch (Exception $e) {
            Log::error('Error fetching user details: ' . $e->getMessage());
            session()->now('error', 'An error occurred while fetching user details.');
            return redirect()->back();
        }
    }

    public function updateProfile(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            DB::beginTransaction();
            $user = User::findOrFail($id);
            $user->update($request->all());
            $user->role_id = $request->input('role_id');
            $user->save();
            DB::commit();
            return redirect()->back()->with('success', 'User updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error fetching user details: ' . $e->getMessage());
            session()->now('error', 'An error occurred while fetching user details.');
            return redirect()->back();
        }
    }

    protected function registerUser(Request $request)
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
                $user->roles()->attach(3); // Ensure role ID exists in your roles table
            } else {
                throw new Exception('User roles relationship not found.');
            }

            // Create Customer profile
            Customer::create([
                'user_id' => $user->id,
                'first_name' => $request->input('first_name', null),
                'last_name' => $request->input('last_name', null),
                'phone' => $request->input('phone', null),
                'address' => $request->input('address', null),
                'city' => $request->input('city', null),
                'state' => $request->input('state', null),
                'zipcode' => $request->input('zipcode', null),
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
