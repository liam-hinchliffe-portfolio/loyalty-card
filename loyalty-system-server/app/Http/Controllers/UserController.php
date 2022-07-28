<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\User;
use \App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login() {
        if (Auth::guard('web')->attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = Auth::user();
            $token = $user->createToken('_token')->accessToken;
            $cookie = cookie('_token', $token, 525600, null, null, null, true, false, 'strict');
            return response()->json(['valid' => true, 'token' => $token, 'user' => $user])->cookie($cookie);
        }
        return response()->json("Incorrect credentials", 403);
    }

    public function authUser (Request $request) {
        $user = Auth::user();
        return response()->json($user);
    }

    public function get(Request $request, $id) {
        $user = User::findOrFail($id);
        return response()->json(['user' => $user]);
    }

    public function index (Request $request) {
        $users = User::all();
        return response(['users' => $users]);
    }

    public function search (Request $request, $searchQuery) {
        $authUser = Auth::user();

        if ($authUser->auth_level === 2)  {
            $users = User::where('id', 'LIKE', '%' . $searchQuery . '%')->orWhere('email', 'LIKE', '%' . $searchQuery . '%')->where('auth_level', '1')->whereNull('deleted_at')->get();
        } else if ($authUser->auth_level === 3) {
            $users = User::where('id', 'LIKE', '%' . $searchQuery . '%')->orWhere('email', 'LIKE', '%' . $searchQuery . '%')->get();
        } else return response()->json("You do not have permission to perform this action", 403);
        
        return response()->json($users);
    }

    public function register (Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'email|max:100|min:4|required|unique:users,email',
            'password' => 'max:100|min:8|required',
            'confirmPassword' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            // get the error messages from the validator
            $messages = $validator->messages();
            // redirect our user back to the form with the errors from the validator
            return response()->json(['errors' => $messages], 422);
        }

        $user = User::create([
            'email' => $request->input('email'),
            'password' =>  Hash::make($request->input('password')),
        ]);

        $token = $user->createToken('_token')->accessToken;
        $cookie = cookie('_token', $token, 525600, null, null, null, true, false, 'strict');
        return response()->json(['valid' => true, 'token' => $token, 'user' => $user])->cookie($cookie);
    }

    public function registerEmployee (Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'email|max:100|min:4|required|unique:users,email',
            'password' => 'max:100|min:8|required',
            'confirmPassword' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            // get the error messages from the validator
            $messages = $validator->messages();
            // redirect our user back to the form with the errors from the validator
            return response()->json(['errors' => $messages], 422);
        }

        $authUser = Auth::user();
        if ($authUser && $authUser->auth_level === 3) {
            $user = User::create([
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'auth_level' => 2
            ]);
            return response()->json(['valid' => true, 'user' => $user]);
        } else return response()->json("You are not authorised to create this user", 403);
    }

    public function addPoints(Request $request, $userId) {
        $authUser = Auth::user();
        $user = User::findOrFail($userId);
        $points = $request->input('points');
        if (intval($points) < 0) return response()->json(["msg" => "The entered points must be more than 0"], 400);
        $newPoints = intval(intval($user->points) + intval($points));

        if (!$points || !intval($points) || !$newPoints || $newPoints <= 0) return response()->json(["msg" => "Quantity of points being added has not been provided or has been set to 0"], 400);
        $user->points = $newPoints;
        $user->total_collected_points += intval($points);
        $user->save();
        $log = Log::create([
            'user_id' => $user->id,
            'modifier_user_id' => $authUser->id,
            'points_change' => $points
        ]);
        return response()->json(['user' => $user, "msg" => "Added points to user"], 202);
    }

    public function removePoints(Request $request, $userId) {
        $user = User::findOrFail($userId);
        $points = $request->input('points');
        if (intval($points) < 0) return response()->json(["msg" => "The entered points must be more than 0"], 400);
        $newPoints = intval(intval($user->points) - intval($points));
        if (!$points || !intval($points)) return response()->json(["msg" => "Quantity of points being removed has not been provided or has been set to 0"], 400);
        if ($newPoints < 0) return response()->json(["msg" => "Quantity of points being removed would result in the customer having less than 0 points. This is not allowed. The maximum amount of points that can be removed from this user is " . $user->points], 400);
        $user->points = $newPoints;
        $user->save();
        $log = Log::create([
            'user_id' => $user->id,
            'modifier_user_id' => $user->id,
            'points_change' => intval(0 - $points)
        ]);
        return response()->json(['user' => $user, 'newPoints' => $newPoints], 202);
    }

    /**
     * Soft delete a user model by marking it as deleted
     * Intended to be used alongside a CRON job to delete the records from the database after X amount of time has passed
     */
    public function softDelete (Request $request, $userId) {
        $authUser = Auth::user();
        if ($authUser->auth_level === 3) {
            $user = User::findOrFail($userId);
            if ($user->deleted_at) return response()->json(["The user cannot be deleted"], 422);
            $user->deleted_at = date('Y-m-d H:i:s');
            $user->save();
            return response()->json(['user' => $user, "msg" => "The user has been deleted"], 202);    
        } else return response()->json("You do not have permission to delete this user", 403);
    }

    public function restore (Request $request, $userId) {
        $authUser = Auth::user();
        if ($authUser->auth_level === 3) {
            $user = User::findOrFail($userId);
            $user->deleted_at = null;
            $user->save();
            return response()->json(['msg' => "The user has been restored"], 202);
        } else return response()->json("You do not have permission to delete this user", 403);
    }

    public function logout (Request $request) {
        $user = Auth::user();
        if (!$user) return response()->json(['msg' => "No user is authenticated"], 404);
        $token = $user->token();
        $token->revoke();
        $cookie = \Cookie::forget('_token');
        return response()->json("Logging out")->cookie($cookie);
    }
}
