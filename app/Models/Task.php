<?php

namespace App\Models;

use App\AbstractAPIModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Group;

class Task extends AbstractAPIModel
{
    use HasFactory;

    protected $fillable = ['title', 'deadline', 'description', 'project_id', 'unique_id', 'user_id', 'category_id'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class)->withPivot('is_supervisor')->withTimestamps();
    }

    public function type()
    {
        return 'tasks';
    }
}
