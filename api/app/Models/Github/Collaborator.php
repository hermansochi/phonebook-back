<?php

namespace App\Models\Github;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class Collaborator extends Model
{
    use HasFactory, Uuid;

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
        'role_name'
    ];

    public function repo()
    {
        return $this->belongsTo(Repo::class);
    }
}
