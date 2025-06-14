<?php

namespace App\Models\module2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\module1\PublicUser;

class Inquiry extends Model
{
    use HasFactory;    protected $table = 'inquiry';
    protected $primaryKey = 'inquiryId';
    public $timestamps = false; // No timestamps in migration
    
    protected $fillable = [
        'title',
        'description',
        'userId',
        'final_status',
        'submission_date',
        'evidenceUrl',
        'evidenceFileUrl'
        // Removed 'assignedTo' - doesn't exist in migration
    ];

    protected $casts = [
        'submission_date' => 'date',
        // Removed array cast - evidenceFileUrl is string in migration
    ];

    // Relationship with PublicUser (module1)
    public function user()
    {
        return $this->belongsTo(PublicUser::class, 'userId');
    }    // Relationship with inquiry assignments
    public function assignments()
    {
        return $this->hasMany(InquiryAssignment::class, 'inquiryId', 'inquiryId');
    }

    // Relationship with status history
    public function statusHistory()
    {
        return $this->hasMany(InquiryStatusHistory::class, 'inquiryId', 'inquiryId');
    }

    // Scope for filtering by status
    public function scopeByStatus($query, $status)
    {
        return $query->where('final_status', $status);
    }

    // Scope for user's inquiries
    public function scopeForUser($query, $userId)
    {
        return $query->where('userId', $userId);
    }    // Get formatted evidence files (comma-separated string to array)
    public function getEvidenceFilesAttribute()
    {
        if (!$this->evidenceFileUrl) {
            return [];
        }
        
        $files = explode(',', $this->evidenceFileUrl);
        return array_filter(array_map('trim', $files));
    }

    // Set evidence files (array to comma-separated string)
    public function setEvidenceFilesAttribute($files)
    {
        if (is_array($files)) {
            $this->attributes['evidenceFileUrl'] = implode(',', $files);
        } else {
            $this->attributes['evidenceFileUrl'] = $files;
        }
    }
}