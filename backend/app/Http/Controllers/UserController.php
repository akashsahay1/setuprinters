<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Staff;
use stdClass;

class UserController extends Controller
{
    public function index()
     {


            $data = new stdClass();
    
            $data->users = Staff::all();
            return response()->json($data->users);

     }
}
