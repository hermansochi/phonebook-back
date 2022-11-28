<?php

namespace App\Models\Github;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class Repo extends Model
{
    use HasFactory, UUids;

    protected $fillable = [
    'id',
    'github_user_id',
    'github_id',
    'name',
    'full_name',
    'private',
    'url',
    'html_url',
    'description',
    'fork',
    'homepage',
    'size',
    'stargazers_count',
    'watchers_count',
    'forks',
    'forks_count',
    'open_issues',
    'open_issues_count',
    'watchers',
    'language',
    'has_issues',
    'has_projects',
    'has_downloads',
    'has_wiki',
    'has_pages',
    'has_discussions',
    'is_template',
    'mirror_url',
    'archived',
    'disabled',
    'allow_forking',
    'visibility',
    'default_branch',
    'github_created_at',
    'github_updated_at',
    'github_pushed_at'
    ];

    public function githubUsers()
    {
        return $this->belongsTo(GithubUser::class);
    }
}
