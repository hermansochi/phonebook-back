<?php

namespace App\Models\Github;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collaborator extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'id',
        'repo_id',
        'github_id',
        'login',
        'avatar_url',
        'url',
        'html_url',
        'repos_url',
        'type',
        'site_admin',
        'permissions',
        'role_name',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function repo()
    {
        return $this->belongsTo(Repo::class);
    }
}
