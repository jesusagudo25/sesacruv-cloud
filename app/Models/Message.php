<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;


    /**
     * get messages for a specific analyst or student
     *
     * return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    
    public function messageable()
    {
        return $this->morphTo();
    }

    /**
     * Get the review for the message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }
}
