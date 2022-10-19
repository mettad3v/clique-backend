<?php

namespace App\Models;

use App\AbstractAPIModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends AbstractAPIModel
{
    use HasFactory;

    protected $fillable = ['name', 'user_id'];
    protected $hidden = ['user_id'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function invitees()
    {
        return $this->belongsToMany(User::class)->withPivot('is_admin')->withTimestamps();
    }

    public function boards()
    {
        return $this->HasMany(Board::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function type()
    {
        return 'projects';
    }
}
