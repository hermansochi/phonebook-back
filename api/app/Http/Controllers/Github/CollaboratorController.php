<?php

namespace App\Http\Controllers\Github;

use App\Http\Controllers\Controller;
use App\Http\Requests\Github\IndexCollaboratorRequest;
use App\Http\Requests\Github\StoreCollaboratorRequest;
use App\Http\Requests\Github\UpdateCollaboratorRequest;
use App\Http\Resources\Github\CollaboratorCollection;
use App\Models\Github\Collaborator;

class CollaboratorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return App\Http\Resources\Github\CollaboratorCollection
     */
    public function index(IndexCollaboratorRequest $request): CollaboratorCollection
    {
        $validated = $request->safe()->only(['page', 'per_page', 'sort']);
        if (! array_key_exists('per_page', $validated)) {
            $validated['per_page'] = 30;
        }
        if (! array_key_exists('sort', $validated) or $validated['sort'] === 'login') {
            $sortField = 'login';
            $sortOrder = 'asc';
        } else {
            $sortField = 'login';
            $sortOrder = 'desc';
        }

        return new CollaboratorCollection(Collaborator::orderBy($sortField, $sortOrder)
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
     * @param  \App\Http\Requests\StoreCollaboratorRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCollaboratorRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Github\Collaborator  $collaborator
     * @return \Illuminate\Http\Response
     */
    public function show(Collaborator $collaborator)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Github\Collaborator  $collaborator
     * @return \Illuminate\Http\Response
     */
    public function edit(Collaborator $collaborator)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCollaboratorRequest  $request
     * @param  \App\Models\Github\Collaborator  $collaborator
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCollaboratorRequest $request, Collaborator $collaborator)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Github\Collaborator  $collaborator
     * @return \Illuminate\Http\Response
     */
    public function destroy(Collaborator $collaborator)
    {
        //
    }
}
