<?php

namespace App\Models;

use App\AbstractAPIModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends AbstractAPIModel
{
    use HasFactory;

    protected $fillable = ['name', 'user_id'];
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function invitees()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
    
    public function tasks()
    {
        return $this->HasMany(Task::class);
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
