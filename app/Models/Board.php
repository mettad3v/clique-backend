<?php

namespace App\Models;

use App\AbstractAPIModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Board extends AbstractAPIModel
{
    use HasFactory;

    protected $fillable = ['user_id', 'project_id', 'title'];
    protected $hidden = ['user_id', 'project_id'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function type()
    {
        return 'boards';
    }
}
