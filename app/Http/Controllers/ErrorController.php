<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ErrorController extends Controller
{
    /**
     * Show the custom 404 error page.
     */
    public function notFound()
    {
        return response()->view('error.404', [], 404);
    }
}
