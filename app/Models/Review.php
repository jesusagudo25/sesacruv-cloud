<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'analyst_id',
        'student_id',
        'date_review',
    ];

    /**
     * Get the analyst that owns the review.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function analyst()
    {
        return $this->belongsTo(Analyst::class);
    }

    /**
     * Get the student that owns the review.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the messages for the review.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
