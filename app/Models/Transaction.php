<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'type', 'user_id', 'company_id', 'category_id', 'from', 'to', 'amount', 'description', 'timestamp', 'status', 'attachment',
    ];

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function category(){
        return $this->belongsTo('App\Models\Category');
    }
    
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }

    public function account(){
        return $this->belongsTo('App\Models\Account', 'from');
    }

    public function target(){
        return $this->belongsTo('App\Models\Account', 'to');
    }
}
