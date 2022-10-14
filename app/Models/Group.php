<?php

namespace App\Models;

use App\AbstractAPIModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends AbstractAPIModel
{
    use HasFactory;

    protected $fillable = ['title', 'user_id', 'project_id'];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function type()
    {
        return 'groups';
    }
}
