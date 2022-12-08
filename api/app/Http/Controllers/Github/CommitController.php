<?php

namespace App\Http\Controllers\Github;

use App\Http\Controllers\Controller;
use App\Http\Requests\Github\IndexCommitRequest;
use App\Http\Requests\Github\StoreCommitRequest;
use App\Http\Requests\Github\UpdateCommitRequest;
use App\Http\Resources\Github\CommitCollection;
use App\Models\Github\Commit;
use Illuminate\Support\Facades\DB;

class CommitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return App\Http\Resources\Github\CommitCollection
     */
    public function index(IndexCommitRequest $request): CommitCollection
    {
        $validated = $request->safe()->only(['page', 'per_page', 'sort']);
        if (! array_key_exists('per_page', $validated)) {
            $validated['per_page'] = 30;
        }
        if (! array_key_exists('sort', $validated) or $validated['sort'] === '-date') {
            $sortField = 'author_date';
            $sortOrder = 'desc';
        } else {
            $sortField = 'author_date';
            $sortOrder = 'asc';
        }

        return new CommitCollection(Commit::orderBy($sortField, $sortOrder)
            ->paginate($validated['per_page']));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function stat()
    {
        //

        $commitsByDay = Commit::where('author_date', '>=', \Carbon\Carbon::now()->subMonth(3))
            ->groupBy('date')
            ->groupBy('author_name')
            ->orderBy('date', 'DESC')
            ->get([
                DB::raw('Date(author_date) as date'),
                DB::raw('author_name'),
                DB::raw('COUNT(*) as "commits"'),
            ]);

        return ['data' => $commitsByDay];
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
     * @param  \App\Http\Requests\StoreCommitRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCommitRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Github\Commit  $commit
     * @return \Illuminate\Http\Response
     */
    public function show(Commit $commit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Github\Commit  $commit
     * @return \Illuminate\Http\Response
     */
    public function edit(Commit $commit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCommitRequest  $request
     * @param  \App\Models\Github\Commit  $commit
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCommitRequest $request, Commit $commit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Github\Commit  $commit
     * @return \Illuminate\Http\Response
     */
    public function destroy(Commit $commit)
    {
        //
    }
}
