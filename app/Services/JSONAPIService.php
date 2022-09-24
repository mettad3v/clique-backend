<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Resources\JSONAPIResource;
use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\JSONAPICollection;
use Illuminate\Support\Facades\Notification;
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
            ->header('Location', route("{$model->type()}.show", [
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
        if ($model->$relationship instanceof Model) {
            return new JSONAPIIdentifierResource($model->$relationship);
        }
        return JSONAPIIdentifierResource::collection($model->$relationship);
    }

    public function updateToOneRelationship($model, $relationship, $id)
    {
        $relatedModel = $model->$relationship()->getRelated();
        $model->$relationship()->dissociate();

        if ($id) {
            $newModel = $relatedModel->newQuery()->findOrFail($id);
            $model->$relationship()->associate($newModel);
        }
        $model->save();
        return response(null, 204);
    }

    public function updateToManyRelationships($model, $relationship, $ids)
    {
        $foreignKey = $model->$relationship()->getForeignKeyName();
        $relatedModel = $model->$relationship()->getRelated();

        $relatedModel->newQuery()->where($foreignKey, $model->id)->update([$foreignKey => null,]);
        $relatedModel->newQuery()->whereIn('id', $ids)->update([$foreignKey => $model->id,]);

        return response(null, 204);
    }

    public function fetchRelated($model, $relationship)
    {
        if ($model->$relationship instanceof Model) {
            return new JSONAPIResource($model->$relationship);
        }
        return new JSONAPICollection($model->$relationship);
    }

    public function updateManyToManyRelationships($model, $relationship, $ids)
    {
        $model->$relationship()->sync($ids);
        return response(null, 204);
    }

    // public function notificationHandler($request, $resource, $relationship, $notification, $defaultNotification, $user)
    // {
    //     if (count($request->input('data.*.id')) > count($resource->$relationship->pluck('id'))) {
    //         $users_to_notify = array_diff($request->input('data.*.id'), $resource->$relationship->pluck('id')->toArray());

    //         $users_to_notify = User::whereIn('id', $users_to_notify)->get();
    //         Notification::send($users_to_notify, new $notification($user, $resource));
    //     } else {
    //         $users_to_notify = array_diff($resource->$relationship->pluck('id')->toArray(), $request->input('data.*.id'));

    //         $users_to_notify = User::whereIn('id', $users_to_notify)->get();
    //         Notification::send($users_to_notify, new $defaultNotification($user, $resource));
    //     }
    // }
}
