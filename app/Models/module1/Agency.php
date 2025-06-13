<?php

namespace App\Models\module1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Agency extends Model
{
    protected $table = 'agency';
    protected $primaryKey = 'agencyId';
    public $timestamps = true;

    protected $fillable = [
        'agency_name',
        'agencyPassword',
        'mcmcId',
        'agencyUsername',
    ];

    // Find agency by username
    public static function findByUsername($username)
    {
        return self::where('agencyUsername', $username)->first();
    }

    // Check password
    public function checkPassword($password)
    {
        return Hash::check($password, $this->agencyPassword);
    }
}