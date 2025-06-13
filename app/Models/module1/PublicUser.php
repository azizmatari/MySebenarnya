<?php

namespace App\Models\module1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class PublicUser extends Model
{
    protected $table = 'publicuser'; // match your migration table name
    protected $primaryKey = 'userId';
    public $timestamps = true;

    protected $fillable = [
        'userName',
        'userEmail',
        'userPassword',
        'userContact_number',
    ];

    // Find user by email
    public static function findByEmail($email)
    {
        return self::where('userEmail', $email)->first();
    }

    // Register new user
    public static function register($data)
    {
        $data['userPassword'] = Hash::make($data['userPassword']);
        return self::create($data);
    }

    // Check password
    public function checkPassword($password)
    {
        return Hash::check($password, $this->userPassword);
    }
}