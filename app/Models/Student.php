<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'identity_card',
        'receipt_number',
    ];
    /**
     * Get the review for the student.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function review(){
        return $this->hasOne(Review::class);
    }

    /**
     * Get the messages for the students.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function messages()
    {
        return $this->morphToMany(Message::class, 'messageable');
    } 
}
