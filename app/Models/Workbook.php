<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Field;
use App\Models\Version;
use App\Models\NamedLink;

class Workbook extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fields()
    {
        return $this->hasMany(Field::class);
    }

    public function versions()
    {
        return $this->hasMany(Version::class);
    }

    public function namedLinks()
    {
        return $this->hasMany(NamedLink::class);
    }
}
