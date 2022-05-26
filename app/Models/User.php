<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Traits\Uuids;
use GuzzleHttp\Handler\Proxy;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Uuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function invitations()
    {
        return $this->belongsToMany(Project::class);
    }

    public function tasksAssigned()
    {
        return $this->belongsToMany(Task::class)->withPivot('is_supervisor')->withTimestamps();
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }


    public function type()
    {
        return 'users';
    }

    public function allowedAttributes()
    {
        return collect($this->attributes)->filter(function (
            $item,
            $key
        ) {
            return !collect($this->hidden)->contains($key) && $key
                !== 'id';
        })->merge([
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
    }
}
