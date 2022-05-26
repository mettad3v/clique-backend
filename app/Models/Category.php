<?php

namespace App\Models;

use App\AbstractAPIModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends AbstractAPIModel
{
    use HasFactory;

    protected $fillable = ['title'];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function type()
    {
        return 'categories';
    }
}
