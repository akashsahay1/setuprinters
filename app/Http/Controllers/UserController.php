<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use stdClass;

class UserController extends Controller
{
    public function index()
     {


            $data = new stdClass();
    
            $data->users = User::where('user_role', '=', 'customer')
            ->get();
            return response()->json($data->users);

     }
}
