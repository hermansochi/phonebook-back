<?php

namespace App\Models\Github;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class GithubUser extends Model
{
    use HasFactory, UUids;

    protected $fillable = [
        'id',
        'role',
        'github_id',
        'avatar_url',
        'url',
        'html_url',
        'repos_url',
        'type',
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
        'github_updated_at'
    ];

    public function repos()
    {
        return $this->hasMany(Repo::class);
    }
}
