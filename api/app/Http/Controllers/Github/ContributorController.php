<?php

namespace App\Http\Controllers\Github;

use App\Http\Controllers\Controller;
use App\Http\Requests\Github\IndexContributorRequest;
use App\Http\Requests\Github\StoreContributorRequest;
use App\Http\Requests\Github\UpdateContributorRequest;
use App\Http\Resources\Github\ContributorCollection;
use App\Models\Github\Contributor;

class ContributorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return App\Http\Resources\Github\ContributorCollection
     */
    public function index(IndexContributorRequest $request): ContributorCollection
    {
        $validated = $request->safe()->only(['page', 'per_page', 'sort']);
        if (! array_key_exists('per_page', $validated)) {
            $validated['per_page'] = 30;
        }
        if (! array_key_exists('sort', $validated) or $validated['sort'] === '-contributions') {
            $sortField = 'contributions';
            $sortOrder = 'desc';
        } else {
            $sortField = 'contributions';
            $sortOrder = 'asc';
        }

        return new ContributorCollection(Contributor::orderBy($sortField, $sortOrder)
            ->paginate($validated['per_page']));
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
     * @param  \App\Http\Requests\StoreContributorRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreContributorRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Github\Contributor  $contributor
     * @return \Illuminate\Http\Response
     */
    public function show(Contributor $contributor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Github\Contributor  $contributor
     * @return \Illuminate\Http\Response
     */
    public function edit(Contributor $contributor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateContributorRequest  $request
     * @param  \App\Models\Github\Contributor  $contributor
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateContributorRequest $request, Contributor $contributor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Github\Contributor  $contributor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contributor $contributor)
    {
        //
    }
}
