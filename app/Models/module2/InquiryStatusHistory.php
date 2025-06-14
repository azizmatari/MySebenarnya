<?php

namespace App\Models\module2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InquiryStatusHistory extends Model
{
    use HasFactory;    protected $table = 'inquirystatushistory';
    protected $primaryKey = 'status_id';
    public $timestamps = false; // No timestamps in migration
    
    protected $fillable = [
        'inquiryId',
        'agencyId',
        'status',
        'status_comment'
    ];

    // No casts needed since no timestamps

    // Relationship with Inquiry
    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class, 'inquiryId', 'inquiryId');
    }

    // Relationship with Agency
    public function agency()
    {
        return $this->belongsTo(\App\Models\module1\Agency::class, 'agencyId', 'agencyId');
    }
}
