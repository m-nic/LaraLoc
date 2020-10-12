<?php

namespace mNic\LaraLoc\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $table = 'translations';

    protected $fillable = [
        'column_name',
        'locale',
        'value',
    ];
}
