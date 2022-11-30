<?php

namespace App\Http\Resources\Github;

use Illuminate\Http\Resources\Json\JsonResource;

class RepoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return parent::toArray($request);
        /*return [
            'id' => $this->id,
            'repo_id' => $this->repo_id,
            'sha' => $this->sha,
            'author_id' => $this->author_id,
            'author_login' => $this->author_login,
            'author_name' => $this->author_name,
            'author_date' => $this->author_date,
            'message' => $this->message,
        ];*/
    }
}
