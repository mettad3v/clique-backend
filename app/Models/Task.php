<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'deadline', 'description', 'project_id', 'user_id', 'category_id'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class);
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function assignees()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
