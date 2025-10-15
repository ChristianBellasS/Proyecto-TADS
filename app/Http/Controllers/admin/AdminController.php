<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\admin\Admin;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }
}        