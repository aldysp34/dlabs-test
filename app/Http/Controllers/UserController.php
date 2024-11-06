<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua pengguna.
     * Endpoint: GET /users
     */
    public function index(Request $request)
    {
        // Ambil parameter page dan limit (batas data per halaman) dengan nilai default jika tidak ada
        $page = $request->input('page', 1); // default page 1
        $limit = $request->input('limit', 10); // default limit 10

        // Tentukan cache key dengan memasukkan query parameters (gunakan limit, bukan per_page)
        $cacheKey = 'users_page_' . $page . '_limit_' . $limit;

        // Cek apakah data ada di cache
        $users = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($limit) {
            // Ambil data pengguna dengan pagination dan limit
            return User::paginate($limit);
        });

        return response()->json($users);
    }

    /**
     * Menampilkan data pengguna berdasarkan ID.
     * Endpoint: GET /users/{id}
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user, 200);
    }

    /**
     * Menambahkan pengguna baru.
     * Endpoint: POST /users
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'age' => 'required|integer|min:1',
            'membership_status' => 'required|in:active,inactive,pending',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'age' => $request->age,
            'membership_status' => $request->membership_status,
        ]);

        Cache::forget('users_page_1_limit_10');

        return response()->json(['message' => 'User created successfully'], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$id}",
            'age' => 'required|integer|min:1',
            'membership_status' => 'required|in:active,inactive,pending',
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 400);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->age = $request->age;
        $user->membership_status = $request->membership_status;
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return response()->json(['message' => 'User updated successfully'], 200);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
