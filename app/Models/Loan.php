<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Loan extends Model
{
    use HasFactory;

    const FINE_PER_DAY = 70; // fine per overdue day in KES
    const DEFAULT_AMOUNT = 500; // default loan amount

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
        'status',
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

    // Dynamic fine calculation
    public function getCalculatedFineAttribute()
    {
        // If returned, use stored fine
        if ($this->status === 'returned') {
            return $this->fine ?? 0;
        }

        if ($this->due_at) {
            $due = $this->due_at instanceof Carbon ? $this->due_at : Carbon::parse($this->due_at);
            $now = Carbon::now();

            if ($now->gt($due)) {
                // Correct order: now()->diffInDays(due) will give positive number of overdue days
                $daysOverdue = $due->diffInDays($now);
                return $daysOverdue * self::FINE_PER_DAY;
            }
        }

        return 0;
    }

    // Dynamic total (amount + fine)
    public function getCalculatedTotalAttribute()
    {
        return ($this->amount ?? self::DEFAULT_AMOUNT) + $this->calculated_fine;
    }

    // Status label
    public function getStatusLabelAttribute()
    {
        if ($this->status === 'returned') {
            return 'Returned';
        }

        if ($this->due_at) {
            $due = $this->due_at instanceof Carbon ? $this->due_at : Carbon::parse($this->due_at);
            return Carbon::now()->gt($due) ? 'Overdue' : 'Borrowed';
        }

        return 'Borrowed';
    }

    // Payment label
    public function getPaymentLabelAttribute()
    {
        return $this->is_paid ? 'Paid' : 'Unpaid';
    }

    // Relationship to Payment
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

}
