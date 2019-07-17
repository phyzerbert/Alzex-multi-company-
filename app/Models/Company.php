<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name', 'description',
    ];

    public function transactions(){
        return $this->hasMany('App\Models\Transaction');
    }

    public function expenses(){
        return $this->hasMany('App\Models\Transaction')->where('type', 1);
    }

    public function incomings(){
        return $this->hasMany('App\Models\Transaction')->where('type', 2);
    }

    public function transfers(){
        return $this->hasMany('App\Models\Transaction')->where('type', 3);
    }

    public function accounts(){
        return $this->hasMany('App\Models\Account');
    }

    public function users(){
        return $this->hasMany('App\User');
    }
    
}
