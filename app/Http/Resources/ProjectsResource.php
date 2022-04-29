<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => (string)$this->id,
            'type' => 'projects',
            'attributes' => [
                'name' => $this->name,
                'user_id' => $this->user_id,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'relationships' => [
                'users' => [
                    'links' => [
                        'self' => route('projects.relationships.users', $this->id),
                        'related' => route('projects.users', $this->id),
                    ],
                    'data' => InviteesIdentifierResource::collection($this->whenLoaded('invitees'))
                ],
            ]

        ];
    }

    private function relations()
    {
        return [
            UsersResource::collection($this->whenLoaded('invitees')),
        ];
    }

    public function included($request)
    {
        return collect($this->relations())
            ->filter(function ($resource) {
                return $resource->collection !== null;
            })
            ->flatMap->toArray($request);
    }

    public function with($request)
    {
        $with = [];
        if ($this->included($request)->isNotEmpty()) {
            $with['included'] = $this->included($request);
        }
        return $with;
    }
}
