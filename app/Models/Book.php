<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'isbn',
        'author_id',
        'category_id',
        'description',
        'cover',
        'total_copies',
        'available_copies',
        'published_at',
        'views',
    ];

    protected $casts = [
        'cover' => 'array',
        'published_at' => 'date',
    ];

    // Relationships
    public function author()
    {
        return $this->belongsTo(Author::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    // likes: many-to-many with users (pivot table book_likes)
    public function likes()
    {
        return $this->belongsToMany(\App\Models\User::class, 'book_likes')->withTimestamps();
    }

    // ratings: many ratings (multiple per user allowed)
    public function ratings()
    {
        return $this->hasMany(\App\Models\BookRating::class);
    }

    // reviews
    public function reviews()
    {
        return $this->hasMany(\App\Models\BookReview::class);
    }

    // helper: is liked by a specific user
    public function isLikedBy($user)
    {
        if (!$user)
            return false;
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    // helper: likes count
    public function likesCount()
    {
        return $this->likes()->count();
    }

    // helper: average rating (float, 1-5)
    public function averageRating()
    {
        if ($this->relationLoaded('ratings')) {
            $avg = $this->ratings->avg('stars');
        } else {
            $avg = $this->ratings()->avg('stars');
        }
        return $avg ? round($avg, 2) : 0;
    }

    // helper: reviews count
    public function reviewsCount()
    {
        return $this->reviews()->count();
    }
}
