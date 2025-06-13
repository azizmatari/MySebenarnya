<?php

namespace App\Models\module1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class MCMC_Staff extends Model
{
    protected $table = 'mcmcstaff';
    protected $primaryKey = 'mcmcId';
    public $timestamps = true;

    protected $fillable = [
        'mcmcName',
        'mcmcEmail',
        'mcmcUsername',
        'mcmcPassword',
    ];

    // Find staff by username
    public static function findByUsername($username)
    {
        return self::where('mcmcUsername', $username)->first();
    }

    // Check password
    public function checkPassword($password)
    {
        return Hash::check($password, $this->mcmcPassword);
    }
}