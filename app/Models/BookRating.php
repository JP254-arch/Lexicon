<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'user_id',
        'stars',
        'comment',
    ];

    /**
     * A rating belongs to a book
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * A rating belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
