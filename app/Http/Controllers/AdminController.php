<?php

namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $ayoo = 'Welcome to the Admin Dashboard';
        return view('admin.index', compact('ayoo'));
    }

    public function users(Request $request)
    {
        $query = $request->input('query');

        // Fetch users with pagination and search functionality
        $users = User::when($query, function ($queryBuilder) use ($query) {
            return $queryBuilder->where('name', 'like', "%{$query}%");
        })->paginate(12);

        return view('admin.index', compact('users', 'query'));
    }


    public function sitemaps()
    {
        $ayoo = 'Sitemap List';
        // Add your logic here to get the sitemap list
        return view('admin.index', compact('ayoo'));
    }

    public function urls()
    {
        $ayoo = 'URL List';
        // Add your logic here to get the URL list
        return view('admin.index', compact('ayoo'));
    }

    public function searchUsers(Request $request)
    {
        $query = $request->input('query');
        $users = User::where('name', 'like', "%{$query}%")->get();
        return response()->json($users);
    }

    public function toggleSuspend(Request $request)
    {
        $user = User::find($request->user_id);

        if ($user) {
            $user->suspended = !$user->suspended;
            $user->save();

            $status = $user->suspended ? 'User suspended successfully.' : 'User unsuspended successfully.';

            return response()->json(['success' => $status]);
        }

        return response()->json(['success' => false]);
    }
}
