<?php

namespace App\Models\Github;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GithubUser extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'id',
        'role',
        'login',
        'github_id',
        'avatar_url',
        'url',
        'html_url',
        'repos_url',
        'type',
        'site_admin',
        'name',
        'company',
        'blog',
        'location',
        'email',
        'hireable',
        'public_repos',
        'public_gists',
        'followers',
        'following',
        'github_created_at',
        'github_updated_at',
    ];

    public function repos()
    {
        return $this->hasMany(Repo::class);
    }
}
