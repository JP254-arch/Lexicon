<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'user_id',
        'borrowed_at',
        'due_at',
        'returned_at',
        'fine',
        'amount',
        'total',
        'is_paid',
        'status'
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'due_at' => 'datetime',
        'returned_at' => 'datetime',
        'is_paid' => 'boolean',
    ];

    // Relationships
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getIsOverdueAttribute()
    {
        return !$this->returned_at && now()->greaterThan($this->due_at);
    }

    public function getFineAttribute($value)
    {
        if ($this->status === 'returned') {
            return $value; // already stored fine
        }

        if ($this->isOverdue) {
            $daysOverdue = Carbon::parse($this->due_at)->diffInDays(now());
            return $daysOverdue * 20;
        }

        return 0;
    }

    public function getTotalAttribute($value)
    {
        return $this->amount + $this->fine;
    }
}
