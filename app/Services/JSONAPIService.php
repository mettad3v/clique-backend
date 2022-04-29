<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Resources\JSONAPIResource;
use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\JSONAPICollection;
use App\Http\Resources\JSONAPIIdentifierResource;

class JSONAPIService
{
    public function fetchResource($model, $id = 0, $type = '')
    {
        if ($model instanceof Model) {
            return new JSONAPIResource($model);
        }
        $query = QueryBuilder::for($model::where('id', $id))
            ->allowedIncludes(config("jsonapi.resources.{$type}.allowedIncludes"))
            ->firstOrFail();
        return new JSONAPIResource($query);
    }

    public function fetchResources(string $modelClass, string $type)
    {
        $resource = QueryBuilder::for($modelClass)
            ->allowedSorts(config("jsonapi.resources.{$type}.allowedSorts"))
            ->allowedIncludes(config("jsonapi.resources.{$type}.allowedIncludes"))
            ->jsonPaginate();

        return new JSONAPICollection($resource);
    }

    public function createResource(string $modelClass, array $attributes)
    {
        $model = $modelClass::create($attributes);
        return (new JSONAPIResource($model))
            ->response()
            ->header('Location', route('{$model->type()}.show', [
                Str::singular($model->type()) => $model,

            ]));
    }

    public function updateResource($model, $attributes)
    {
        $model->update($attributes);
        return new JSONAPIResource($model);
    }

    public function deleteResource($model)
    {
        $model->delete();
        return response(null, 204);
    }

    public function fetchRelationship($model, string $relationship)
    {
        return JSONAPIIdentifierResource::collection($model->$relationship);
    }

    public function fetchRelated($model, $relationship)
    {
        return new JSONAPICollection($model->$relationship);
    }
}
