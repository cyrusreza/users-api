<?php

// app/Http/Controllers/Api/UserController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCreated;

class UserController extends Controller
{
    // Get all users
    public function index(Request $request)
    {
        // Set the page size, or use the default page size
        $perPage = $request->input('per_page', 10); // Default is 10 users per page
        
        // Get the search term from the request query parameters (e.g., name, email)
        $searchTerm = $request->input('search', '');

        // Get the sorting field and direction from the request (defaults to 'name' and 'asc')
        $sortBy = $request->input('sort_by', 'name'); // Default sorting by 'name'
        $sortDirection = $request->input('sort_direction', 'asc'); // Default sorting direction is 'asc'

        // Validate that the sort direction is either 'asc' or 'desc'
        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $sortDirection = 'asc'; // Fallback to 'asc' if an invalid direction is provided
        }

        // Build the query to search users
        $query = User::query();

        // Filter by name if 'search' term is provided
        if ($searchTerm) {
            $query->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }

         // Apply sorting by the specified field and direction
         $query->orderBy($sortBy, $sortDirection);

        // Apply pagination
        $users = $query->paginate($perPage);

        // Return paginated response
        return response()->json([
            'page' => $users->currentPage(),
            'per_page' => $users->perPage(),
            'total' => $users->total(),
            'users' => $users->items(),
        ]);
    }

    // Get a single user by ID
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    // Create a new user
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Send the welcome email
        Mail::to($user->email)->send(new UserCreated($user));

        return response()->json($user, 201);
    }

    // Update a user
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email,' . $user->id,
            'password' => 'string|min:6',
        ]);

        $user->update($request->only(['name', 'email', 'password']));

        // Optionally hash the password
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        return response()->json($user);
    }

    // Delete a user
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}

