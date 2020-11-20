<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Localisation;

class LocalisationController extends Controller
{
    public static function get(Request $request) {
        return Localisation::where('lang', $request->lang)->where('string', $request->string)->first()->translated;
    }
}
