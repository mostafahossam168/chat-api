<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;
    protected $fillable = [
        'created_by',
        'name',
        'is_private',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants()
    {
        return $this->hasMany(ChatParticipant::class, 'chat_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class)->latest();
    }



    public function scopeHasParticipant($query, $userId)
    {
        return $query->whereHas('participants', function ($q) use ($userId) {
            $q->orWhere('user_id', $userId);
        });
    }


    public function scopeHasCreatedBy($query, $userId)
    {
        return $query->whereHas('participants', function ($q) use ($userId) {
            $q->orWhere('created_by', $userId);
        });
    }
}
