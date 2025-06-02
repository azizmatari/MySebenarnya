<?php 
namespace App\Models;

class Message
{
    public static function all()
    {
        return [
            'Laravel is awesome!',
            'You are testing your setup!',
            'This is coming from the model!'
        ];
    }
}
