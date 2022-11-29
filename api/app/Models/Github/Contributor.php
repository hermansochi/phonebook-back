<?php

namespace App\Models\Github;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class Contributor extends Model
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
        'contributions',
    ];

    public function repo()
    {
        return $this->belongsTo(Repo::class);
    }
}
