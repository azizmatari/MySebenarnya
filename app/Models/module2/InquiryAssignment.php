<?php

namespace App\Models\module2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\module1\Agency;

class InquiryAssignment extends Model
{
    use HasFactory;    protected $table = 'inquiryassignment';
    protected $primaryKey = 'assignmentId';
    public $timestamps = false; // No timestamps in migration
    
    protected $fillable = [
        'inquiryId',
        'agencyId',
        'mcmcComments',
        'isRejected',
        'mcmcId',
        'assignDate'
    ];    protected $casts = [
        'assignDate' => 'date',
        'isRejected' => 'boolean'
    ];

    // Relationship with Inquiry
    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class, 'inquiryId', 'inquiryId');
    }

    // Relationship with Agency (module1)
    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }
}
