<?php

namespace App\Models\module4;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    protected $table = 'agency';
    protected $primaryKey = 'agencyId';
    public $timestamps = false;

    protected $fillable = [
        'agency_name',
        'agencyPassword',
        'mcmcId',
        'agencyUsername',
        'profile_picture',
        'agencyType',
        'agencyContact'
    ];

    /**
     * Get the inquiries assigned to this agency
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(InquiryAssignment::class, 'agencyId', 'agencyId');
    }

    /**
     * Get the status history updates made by this agency
     */
    public function statusUpdates(): HasMany
    {
        return $this->hasMany(InquiryStatusHistory::class, 'agencyId', 'agencyId');
    }

    /**
     * Get active inquiries assigned to this agency
     */
    public function activeInquiries()
    {
        return $this->assignments()
                    ->whereHas('inquiry', function($query) {
                        $query->whereNotIn('final_status', [
                            'True',
                            'Fake',
                            'Rejected'
                        ]);
                    });
    }

    /**
     * Get resolved inquiries by this agency
     */
    public function resolvedInquiries()
    {
        return $this->assignments()
                    ->whereHas('inquiry', function($query) {
                        $query->whereIn('final_status', [
                            'True',
                            'Fake',
                            'Rejected'
                        ]);
                    });
    }

    /**
     * Get the MCMC staff member who registered this agency
     */
    public function mcmcStaff()
    {
        return $this->belongsTo(\App\Models\module1\Mcmc::class, 'mcmcId', 'mcmcId');
    }
}
