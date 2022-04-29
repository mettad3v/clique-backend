<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\MissingValue;
use Illuminate\Http\Resources\Json\JsonResource;

class JSONAPIResource extends JsonResource
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
            'type' => $this->type(),
            'attributes' => $this->allowedAttributes(),
        ];
    }

    private function prepareRelationships()
    {
        return collect(config("jsonapi.resources.{$this->type()}.relationships"))
            ->flatMap(function ($related) {
                $relatedType = $related['type'];
                $relationship = $related['method'];
                return [
                    $relatedType => [
                        'links' => [
                            'self' => route("{$this->type()}.relationships.{$relatedType}", $this->id),
                            'related' => route("{$this->type()}.{$relatedType}", $this->id),
                        ],
                        'data' => !$this->whenLoaded($relationship) instanceof MissingValue ?
                            JSONAPIIdentifierResource::collection($this->{$relationship}) : new MissingValue(),
                    ],
                ];
            });
    }

    private function relations()
    {
        return collect(config("jsonapi.resources.{$this->type()}.relationships"))
            ->map(function ($relation) {
                return JSONAPIResource::collection($this->whenLoaded($relation['method']));
            });
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
