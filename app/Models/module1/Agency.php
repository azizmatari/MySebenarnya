<?php

namespace App\Models\module1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Agency extends Model
{
    // Table name for agencies
    protected $table = 'agency';
    // Primary key for agencies
    protected $primaryKey = 'agencyId';
    // Set to true if your table uses created_at/updated_at
    public $timestamps = false;

    // Fields that can be mass assigned
    protected $fillable = [
        'agency_name',      // Agency name
        'agencyUsername',   // Login username
        'agencyPassword',   // Hashed password
        'agencyContact',    // Contact details (phone/email)
        'profile_picture',  // Path to profile picture (nullable)
        'mcmcId',           // Foreign key to MCMC staff who registered this agency
    ];

    // Hide sensitive fields when serializing
    protected $hidden = [
        'agencyPassword',
    ];

    /**
     * Find an agency by username.
     */
    public static function findByUsername($username)
    {
        return self::where('agencyUsername', $username)->first();
    }

    /**
     * Check if the provided password matches the agency's password.
     */
    public function checkPassword($password)
    {
        return Hash::check($password, $this->agencyPassword);
    }

    /**
     * Get the agency's profile picture path or null.
     */
    public function getProfilePictureUrl()
    {
        return $this->profile_picture ? asset('storage/' . $this->profile_picture) : null;
    }
}