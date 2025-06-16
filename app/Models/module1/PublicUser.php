<?php


namespace App\Models\module1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class PublicUser extends Model
{
    // Table name for public users
    protected $table = 'publicuser';
    // Primary key for public users
    protected $primaryKey = 'userId';
    // No timestamps in the table
    public $timestamps = false;

    // Fields that can be mass assigned
    protected $fillable = [
        'userName',             // User's full name
        'userEmail',            // Email address
        'userPassword',         // Hashed password
        'userContact_number',   // Contact number
        'profile_picture',      // Path to profile picture (nullable)
    ];

    // Hide sensitive fields when serializing
    protected $hidden = [
        'userPassword',
    ];

    /**
     * Find a public user by email address.
     */
    public static function findByEmail($email)
    {
        return self::where('userEmail', $email)->first();
    }

    /**
     * Register a new public user.
     */
    public static function register($data)
    {
        $data['userPassword'] = Hash::make($data['userPassword']);
        return self::create($data);
    }

    /**
     * Check if the provided password matches the user's password.
     */
    public function checkPassword($password)
    {
        return Hash::check($password, $this->userPassword);
    }

    /**
     * Get the user's profile picture URL or null.
     */
    public function getProfilePictureUrl()
    {
        return $this->profile_picture ? asset('storage/' . $this->profile_picture) : null;
    }

    /**
     * Get user statistics including counts and percentages
     * 
     * @return array Contains counts and percentage breakdown of all user types
     */
    public static function getUserStats()
    {
        $publicCount = self::count();
        $agencyCount = Agency::count();
        $staffCount = MCMC::count();
        $total = $publicCount + $agencyCount + $staffCount;

        return [
            'counts' => [
                'publicCount' => $publicCount,
                'agencyCount' => $agencyCount,
                'staffCount' => $staffCount,
                'total' => $total
            ],
            'percentages' => [
                'publicPercent' => $total > 0 ? round(($publicCount / $total * 100) * 10) / 10 : 0,
                'agencyPercent' => $total > 0 ? round(($agencyCount / $total * 100) * 10) / 10 : 0,
                'staffPercent' => $total > 0 ? round(($staffCount / $total * 100) * 10) / 10 : 0
            ]
        ];
    }
}
