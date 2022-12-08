<?php

namespace App\Http\Controllers\Github;

use App\Http\Controllers\Controller;
use App\Http\Requests\Github\IndexRepoRequest;
use App\Http\Requests\Github\StoreRepoRequest;
use App\Http\Requests\Github\UpdateRepoRequest;
use App\Http\Resources\Github\RepoCollection;
use App\Models\Github\Repo;

class RepoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return App\Http\Resources\Github\RepoCollection
     */
    public function index(IndexRepoRequest $request): RepoCollection
    {
        $validated = $request->safe()->only(['page', 'per_page', 'sort']);
        if (! array_key_exists('per_page', $validated)) {
            $validated['per_page'] = 30;
        }
        if (! array_key_exists('sort', $validated)) {
            $sortField = 'github_pushed_at';
            $sortOrder = 'desc';
        } else {
            switch ($validated['sort']) {
                case 'name':
                    $sortField = 'name';
                    $sortOrder = 'asc';
                    break;
                case '-name':
                    $sortField = 'name';
                    $sortOrder = 'desc';
                    break;
                case 'size':
                    $sortField = 'size';
                    $sortOrder = 'asc';
                    break;
                case '-size':
                    $sortField = 'size';
                    $sortOrder = 'desc';
                    break;
                case 'pushed':
                    $sortField = 'github_pushed_at';
                    $sortOrder = 'asc';
                    break;
                case '-pushed':
                    $sortField = 'github_pushed_at';
                    $sortOrder = 'desc';
                    break;
                case 'created':
                    $sortField = 'github_created_at';
                    $sortOrder = 'asc';
                    break;
                case '-created':
                    $sortField = 'github_created_at';
                    $sortOrder = 'desc';
                    break;
            }
        }

        return new RepoCollection(
            Repo::where('visibility', '=', 'public')
                ->orderBy($sortField, $sortOrder)
                ->paginate($validated['per_page'])
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreRepoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRepoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Github\Repo  $repo
     * @return \Illuminate\Http\Response
     */
    public function show(Repo $repo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Github\Repo  $repo
     * @return \Illuminate\Http\Response
     */
    public function edit(Repo $repo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRepoRequest  $request
     * @param  \App\Models\Github\Repo  $repo
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRepoRequest $request, Repo $repo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Github\Repo  $repo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Repo $repo)
    {
        //
    }
}
