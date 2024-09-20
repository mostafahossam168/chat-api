<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_by' => $this->created_by,
            'name' => $this->name,
            'is_private' => $this->is_private,
            "participants" => ChatParticipantResource::collection($this->participants),
            'last_message' => new MessageResource($this->lastMessage),
            'messages' => MessageResource::collection($this->messages),
        ];
    }
}