<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;
    
    protected $fillable = [

        'loan_id',
        'collected_by',
        'amount_paid',
        'payment_mode',
        'location',
        'collected_at',
        'remarks'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}