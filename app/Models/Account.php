<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'name', 'company_id', 'comment' , 'balance',
    ];

    public function expenses(){
        return $this->hasMany('App\Models\Transaction', 'from');
    }
    
    public function incomings(){
        return $this->hasMany('App\Models\Transaction', 'to');
    }  

    public function company(){
        return $this->belongsTo('App\Models\Company');
    }

}
