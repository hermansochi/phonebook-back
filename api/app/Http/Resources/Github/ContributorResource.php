<?php

namespace App\Http\Resources\Github;

use Illuminate\Http\Resources\Json\JsonResource;

class ContributorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        return [
            'id' => $this->id,
            'github_id' => $this->repo_id,
            'login' => $this->login,
            'avatar_url' => $this->avatar_url,
            'html_url' => $this->html_url,
            'repos_url' => $this->repos_url,
            'contributions' => $this->contributions
        ];
    }
}
