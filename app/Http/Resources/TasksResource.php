<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TasksResource extends JsonResource
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
            'type' => 'tasks',
            'attributes' => [
                'title' => $this->title,
                'description' => $this->description,
                'deadline' => Carbon::parse($this->deadline)->diffForHumans(),
                'unique_id' => $this->unique_id,
                'user_id' => $this->user_id,
                'project_id' => $this->project_id,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]
        ];
    }
}
