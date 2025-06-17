<?php

namespace App\Models\SharedModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\module4\Inquiry;

class InquiryStatusHistory extends Model
{
    protected $table = 'inquirystatushistory';
    protected $primaryKey = 'status_id';
    public $timestamps = true; // Enable timestamps since we added them to the migration

    protected $fillable = [
        'inquiryId',
        'agencyId',
        'status',
        'status_comment',
        'updated_by_agent_id',
        'officer_name',
        'supporting_document'
    ];

    // Status constants
    const STATUS_UNDER_INVESTIGATION = 'Under Investigation';
    const STATUS_TRUE = 'True';
    const STATUS_FAKE = 'Fake';
    const STATUS_REJECTED = 'Rejected';

    /**
     * Get the inquiry this status belongs to
     */
    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class, 'inquiryId', 'inquiryId');
    }    /**
     * Get the agency that updated this status
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(\App\Models\module1\Agency::class, 'agencyId', 'agencyId');
    }    /**
     * Get formatted date using the timestamps
     */
    public function getFormattedDateAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('M j, Y g:i A') : 'Status Updated';
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColorAttribute()
    {
        switch ($this->status) {
            case self::STATUS_UNDER_INVESTIGATION:
                return 'warning';
            case self::STATUS_TRUE:
                return 'success';
            case self::STATUS_FAKE:
                return 'danger';
            case self::STATUS_REJECTED:
                return 'secondary';
            default:
                return 'info';
        }
    }
      /**
     * Get formatted comment for display
     */
    public function getFormattedCommentAttribute()
    {
        $formatted = '';
        if ($this->officer_name) {
            $formatted .= "Reviewing Officer: " . $this->officer_name . "\n";
        }
        if ($this->status_comment) {
            $formatted .= "Notes: " . $this->status_comment;
        }
        
        return $formatted ?: 'No additional comments';
    }
}
