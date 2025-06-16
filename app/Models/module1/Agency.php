<?php

namespace App\Models\module1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Agency extends Model
{
    // Table name for agencies
    protected $table = 'agency';
    // Primary key for agencies
    protected $primaryKey = 'agencyId';    // Set to true if your table uses created_at/updated_at
    public $timestamps = false;    // Fields that can be mass assigned
    protected $fillable = [
        'agency_name',      // Agency name
        'agencyUsername',   // Login username
        'agencyPassword',   // Hashed password
        'agencyContact',    // Contact details (phone/email)
        'profile_picture',  // Path to profile picture (nullable)
        'mcmcId',           // Foreign key to MCMC staff who registered this agency
        'agencyType',       // Agency type (string)
        'first_login',      // First login flag (boolean)
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
    /**
     * Get all available agency types
     * 
     * @return array Available agency types
     */
    public static function getAgencyTypes()
    {
        // Fixed types matching the enum values in the migration
        return ['Education', 'Police', 'Sports', 'Health'];
    }

    /**
     * COMMENTED OUT - Dynamic agency types functionality for future use
     * 
     * Code for dynamic agency types using JSON file storage
     * Uncomment and modify the migration to use string instead of enum when needed
     */
    /*
    public static function getAgencyTypes_dynamic()
    {
        // The default types - these will always be included
        $defaultTypes = ['Education', 'Police', 'Sports', 'Health'];

        // Check if there's a custom types file
        $typesFile = storage_path('app/agency_types.json');
        if (file_exists($typesFile)) {
            $customTypes = json_decode(file_get_contents($typesFile), true);
            if (is_array($customTypes) && !empty($customTypes)) {
                // Merge custom types with default types and remove duplicates
                return array_values(array_unique(array_merge($defaultTypes, $customTypes)));
            }
        }

        return $defaultTypes;
    }
    */

    /**
     * COMMENTED OUT - Save agency types to storage
     * For future use with dynamic agency types
     * 
     * @param array $types
     * @return bool
     */
    /*
    public static function saveAgencyTypes(array $types)
    {
        // Get existing custom types
        $typesFile = storage_path('app/agency_types.json');
        $existingTypes = [];
        if (file_exists($typesFile)) {
            $existingTypes = json_decode(file_get_contents($typesFile), true) ?: [];
        }
        
        // Merge new types with existing types
        $allTypes = array_values(array_unique(array_merge($existingTypes, $types)));
        
        // Write back to file
        return file_put_contents($typesFile, json_encode($allTypes)) !== false;
    }
    */
}
