<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GroupsResource extends JsonResource
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
            'id' => (string) $this->id,
            'type' => 'groups',
            'attributes' => [
                'title' => $this->title,
                'user_id' => $this->user_id,
                'project_id' => $this->project_id,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ], 'relationships' => [
                'tasks' => [
                    'links' => [
                        'self' => route('groups.relationships.tasks', $this->id),
                        'related' => route('groups.tasks', $this->id),
                    ],
                    'data' => GroupsIdentifierResource::collection($this->tasks),
                ],
            ],
        ];
    }
}
