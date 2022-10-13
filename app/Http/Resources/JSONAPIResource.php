<?php

namespace App\Http\Resources;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'relationships' => $this->prepareRelationships()
        ];
    }

    private function prepareRelationships()
    {
        $collection = collect(config("jsonapi.resources.{$this->type()}.relationships"))
            ->flatMap(function ($related) {
                $relationship = $related['method'];
                $relatedType = $related['type'];
                $relatedType2 = $related['type'];

                //check if given val for relationship is equal to its singluar form
                if ($this->$relationship() instanceof BelongsTo) {
                    $relatedType2 = Str::singular($related['type']);
                    // dd($relationship, $relatedType2);
                    // $relatedType = Str::plural($related['type']);
                }
                // dd($relatedType2);

                return [
                    $relationship => [
                        'links' => [
                            'self' => route("{$this->type()}.relationships.{$relationship}", $this->id),
                            'related' => route("{$this->type()}.{$relationship}", $this->id),
                        ],
                        'data' => $this->prepareRelationshipData($relatedType, $relationship),
                    ],
                ];
            });

        //remove the relationships member if the included query param is not used in uri
        return $collection->count() > 0 ? $collection : new MissingValue();
    }

    private function prepareRelationshipData($relatedType, $relationship)
    {
        if ($this->whenLoaded($relationship) instanceof MissingValue) {
            return new MissingValue();
        }
        if ($this->$relationship() instanceof BelongsTo) {
            return new JSONAPIIdentifierResource($this->$relationship);
        }
        return JSONAPIIdentifierResource::collection($this->$relationship);
    }

    public function with($request)
    {
        $with = [];
        if ($this->included($request)->isNotEmpty()) {
            $with['included'] = $this->included($request);
        }
        return $with;
    }

    public function included($request)
    {
        return collect($this->relations())
            ->filter(function ($resource) {
                return $resource->collection !== null;
            })
            ->flatMap(function ($resource) use ($request) {
                return $resource->toArray($request);
            });
    }

    private function relations()
    {
        return collect(config("jsonapi.resources.{$this->type()}.relationships"))
            ->map(function ($relation) {
                $modelOrCollection = $this->whenLoaded($relation['method']);

                //return a single resource for one-to query param but a collection for many-to query param
                if ($modelOrCollection instanceof Model) {
                    $modelOrCollection = collect([new JSONAPIResource($modelOrCollection)]);
                }
                return JSONAPIResource::collection($modelOrCollection);
            });
    }
}
