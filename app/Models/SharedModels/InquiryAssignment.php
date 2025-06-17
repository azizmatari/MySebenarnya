<?php

namespace App\Models\SharedModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\module4\Inquiry;

class InquiryAssignment extends Model
{
    protected $table = 'inquiryassignment';
    protected $primaryKey = 'assignmentId';
    public $timestamps = false;    protected $fillable = [
        'inquiryId',
        'agencyId',
        'mcmcComments',
        'isRejected',
        'mcmcId',
        'assignDate'
    ];

    protected $casts = [
        'assignDate' => 'date',
        'isRejected' => 'boolean',
    ];

    /**
     * Get the inquiry this assignment belongs to
     */
    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class, 'inquiryId', 'inquiryId');
    }    /**
     * Get the assigned agency
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(\App\Models\module1\Agency::class, 'agencyId', 'agencyId');
    }    /**
     * Get the MCMC staff who made the assignment
     */
    public function mcmcStaff(): BelongsTo
    {
        return $this->belongsTo(\App\Models\module1\Mcmc::class, 'mcmcId', 'mcmcId');
    }/**
     * Get formatted assignment date
     */
    public function getFormattedAssignmentDateAttribute()
    {
        return $this->assignDate ? $this->assignDate->format('M d, Y') : 'N/A';
    }
}
