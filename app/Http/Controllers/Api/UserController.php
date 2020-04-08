<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function info(Request $request)
    {
        $tgid = $request->get('tg_id');

        $user = User::where('tg_id', $tgid)->first();
        return response()->json($user);
    }
}
