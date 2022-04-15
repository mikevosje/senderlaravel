<?php

namespace App\Packages\Sender\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Sending extends Model implements HasMedia
{
    protected $guarded = [];

    use InteractsWithMedia;
}
