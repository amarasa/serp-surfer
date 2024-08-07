<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $ayoo = 'Welcome to the Admin Dashboard';
        return view('admin.index', compact('ayoo'));
    }

    public function users()
    {
        $ayoo = 'User List';
        // Add your logic here to get the user list
        return view('admin.index', compact('ayoo'));
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
}
