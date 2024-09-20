<?php

namespace App\Http\Controllers\Api;

use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChatResource;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'is_private' => "nullable|boolean",
        ]);
        if ($validation->fails()) {
            return errorResponse($validation->errors());
        }

        $isPrivate = $request->filled('is_private') ? $request->is_private : 1;

        $chats = Chat::where('is_private', $isPrivate)
            ->where(function ($query) {
                $query->where('created_by', auth()->user()->id)
                    ->orWhereHas('participants', function ($q) {
                        $q->where('user_id', auth()->user()->id);
                    });
            })
            ->with('participants.user', 'lastMessage.user')
            ->get();
        return successResponse(ChatResource::collection($chats));
        // return successResponse($chats);
    }


    public function create(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'user_id' => "required|exists:users,id",
            'name' => "nullable",
            'is_private' => "nullable|boolean",
        ]);
        if ($validation->fails()) {
            return errorResponse($validation->errors());
        }


        if ($request->user_id == auth()->user()->id) {
            return errorResponse('لايمكن عمل شات مع نفسك');
        }

        $previousChat = $this->previousChat($request->user_id);
        if ($previousChat == null) {
            $chat = auth()->user()->chats()->create([
                'is_private' => $request->is_private,
                'name' => $request->name,
            ]);

            $chat->participants()->create([
                'user_id' => $request->user_id
            ]);

            $chat->refresh()->load('participants.user', 'lastMessage.user');
            return successResponse(new ChatResource($chat), 'تم انشاء الشات بنجاح');
            // return successResponse($chat, 'تم انشاء الشات بنجاح');
        }

        return successResponse(new ChatResource($previousChat), 'الشات موجود مسبقا');
        // return successResponse($previousChat, 'الشات موجود مسبقا');
    }


    private function previousChat($otherId)
    {
        return Chat::where('is_private', 1)
            ->where(function ($query) use ($otherId) {
                $query->where('created_by', auth()->user()->id)
                    ->whereHas('participants', function ($subQuery) use ($otherId) {
                        $subQuery->where('user_id', $otherId);
                    });
            })
            ->orWhere(function ($query) use ($otherId) {
                $query->where('created_by', $otherId)
                    ->whereHas('participants', function ($subQuery) {
                        $subQuery->where('user_id', auth()->user()->id);
                    });
            })
            ->first();
    }


    public function show($id)
    {
        $chat = Chat::where('id', $id)->where('is_private', 1)
            ->hasParticipant(auth()->user()->id)
            ->hasCreatedBy(auth()->user()->id)
            ->with('participants.user', 'lastMessage.user')
            ->first();
        if ($chat) {
            $chat->load('participants.user', 'lastMessage.user');
            // return successResponse($chat);
            return successResponse(new ChatResource($chat));
        }
        return errorResponse('الشات غير موجود');
    }
}