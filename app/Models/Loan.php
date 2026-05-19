<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Collection;

class Loan extends Model
{
     use HasFactory;
     
    protected $fillable = [

        'loan_no',
        'customer_name',
        'mobile',
        'address',
        'loan_amount',
        'emi_amount',
        'total_paid',
        'pending_amount',
        'status',
        'created_by'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }
}