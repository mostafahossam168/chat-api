<?php

namespace App\Http\Controllers\Api;

use App\Events\NewMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }


    public function index($chatId)
    {
        $messages = ChatMessage::where('chat_id', $chatId)
            ->with('user')
            ->latest('created_at')->paginate(20);
        // return successResponse($messages);
        return successResponse(MessageResource::collection($messages));
    }



    public function create(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'chat_id' => "required|exists:chats,id",
            'message' => "required|string",
        ]);

        if ($validation->fails()) {
            return errorResponse($validation->errors());
        }

        $message = auth()->user()->chatMessages()->create($validation->validated());

        $message->load('user', 'chat');
        //Send Notifications
        // $this->sendNotification($message);

        return successResponse(new MessageResource($message), 'تم ارسال الرساله بنجاح');
        // return successResponse($message, 'تم ارسال الرساله بنجاح');
    }



    private function sendNotification(ChatMessage $chatMessage)
    {
        // broadcast(new NewMessageSent($chatMessage))->toOthers();

        $userId = auth()->user()->id;
        $chat = Chat::where('id', $chatMessage->chat_id)->with([
            'participants' => function ($query) {
                $query->where('user_id', '!=', auth()->user()->id);
            }
        ])->first();
        if (count($chat->participants) > 0) {
            $otherUser = User::where('id', $chat->participants[0]->user_id)->first();
            $otherUser->sentNewMessageNotifications([
                'messageData' => [
                    'senderName' => auth()->user()->name,
                    'message' => $chatMessage->message,
                    'chatId' => $chatMessage->chat_id
                ],
            ]);
        }
    }
}