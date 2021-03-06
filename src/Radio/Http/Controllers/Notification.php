<?php

namespace Kregel\Radio\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Kregel\Radio\Models\Notification as Notify;

class Notification extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = $this->getUserFromToken($request);

        if(empty($user))
        {
            return $this->unauthorizedUser();
        }
        
        $data = $request->all() + [
            'user_id' => auth()->user()->id
        ];

        Notify::create($data);
        return response()->json([
            'message' => 'Notification made!',
            'code' => $request->ajax() ? 202 : 200
        ]);
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if(empty($user))
        {
            return $this->unauthorizedUser();
        }

        $notification = Notify::find($id);
        if(empty($notification) || $notification->user_id !== $user->id)
        {
            return response()->json([
                'message' => 'No notification found!',
                'code' => 404
            ], 404);
        }
        $notification->is_unread = 0;
        $notification->save();

        return response()->json([
            'message' => 'Notification read!',
            'code' => $request->ajax() ? 202 : 200
        ]);

    }

    private function getUserFromToken($request){
        $token = str_replace('Bearer ', '', $request->get('token'));

        return JWTAuth::parseToken('bearer', 'authorization', $token)->authenticate();
    }

    private function unauthorizedUser(){
        return response()->json([
            'message' => 'You must be logged in to view this!',
            'code' => 403
        ], 403);
    }
}
