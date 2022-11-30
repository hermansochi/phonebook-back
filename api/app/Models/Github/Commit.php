<?php

namespace App\Models\Github;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class Commit extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'id',
        'repo_id',
        'sha',
        'author_id',
        'author_login',
        'author_name',
        'author_date',
        'committer_id',
        'committer_login',
        'committer_name',
        'committer_date',
        'message'
    ];

    protected $casts = [
        'author_date' => 'datetime',
        'committer_date' => 'datetime',
    ];

    public function repo()
    {
        return $this->belongsTo(Repo::class);
    }
}
