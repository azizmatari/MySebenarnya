<?php

namespace App\Models\module4;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\module1\PublicUser;
use App\Models\SharedModels\InquiryStatusHistory;
use App\Models\SharedModels\InquiryAssignment;

class Inquiry extends Model
{
    protected $table = 'inquiry';
    protected $primaryKey = 'inquiryId';
    public $timestamps = false;

    protected $fillable = [
        'userId',
        'title',
        'description',
        'final_status',
        'submission_date',
        'evidenceUrl',
        'evidenceFileUrl'
    ];

    protected $casts = [
        'submission_date' => 'date',    ];    // Status constants
    const STATUS_UNDER_INVESTIGATION = 'Under Investigation';
    const STATUS_TRUE = 'True';
    const STATUS_FAKE = 'Fake';
    const STATUS_REJECTED = 'Rejected';
    const STATUS_REASSIGNED = 'Reassigned';
    const STATUS_REASSIGNMENT_REJECTED = 'Rejected';    public static function getStatuses()
    {
        return [
            self::STATUS_UNDER_INVESTIGATION,
            self::STATUS_TRUE,
            self::STATUS_FAKE,
            self::STATUS_REJECTED,
            self::STATUS_REASSIGNED,
            self::STATUS_REASSIGNMENT_REJECTED,
        ];
    }

    /**
     * Get the user who submitted this inquiry
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(PublicUser::class, 'userId', 'userId');
    }

    /**
     * Get the status history for this inquiry
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(InquiryStatusHistory::class, 'inquiryId', 'inquiryId')
                    ->orderBy('status_id', 'desc');
    }

    /**
     * Get the latest status from history
     */
    public function latestStatus()
    {
        return $this->statusHistory()->first();
    }

    /**
     * Get the assignment for this inquiry
     */
    public function assignment(): HasMany
    {
        return $this->hasMany(InquiryAssignment::class, 'inquiryId', 'inquiryId');
    }

    /**
     * Get the current assigned agency
     */
    public function currentAssignment()
    {
        return $this->assignment()->latest('assignmentId')->first();
    }    /**
     * Get the display status (either from final_status or latest history)
     */
    public function getDisplayStatusAttribute()
    {
        if ($this->final_status) {
            return $this->final_status;
        }

        $latestStatus = $this->latestStatus();
        return $latestStatus ? $latestStatus->status : 'Pending';
    }

    /**
     * Get the current status (either from final_status or latest history)
     */
    public function getCurrentStatus()
    {
        if ($this->final_status) {
            return $this->final_status;
        }

        $latestStatus = $this->latestStatus();
        return $latestStatus ? $latestStatus->status : 'Pending';
    }

    /**
     * Get status color for display
     */
    public function getStatusColorAttribute()
    {
        $status = $this->display_status;
        
        switch ($status) {
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
     * Get the submission date formatted
     */
    public function getFormattedSubmissionDateAttribute()
    {
        return $this->submission_date->format('M d, Y');
    }

    /**
     * Scope to get inquiries for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('userId', $userId);
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('final_status', $status);
    }

    /**
     * Scope to search by title or description
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
