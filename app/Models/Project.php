<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
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
}
