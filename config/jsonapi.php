<?php

return  [
    'resources' => [
        'users' => [
            'allowedSorts' => [
                'name',
                'created_at',
                'updated_at',
            ],
            'allowedIncludes' => [
                'invitations',
                'projects',
                'tasksAssigned',
            ],
            'validationRules' => [
                'create' => [
                    'data.attributes.name' => 'required|string|max:255',
                    'data.attributes.email' => 'required|string|email|unique:users,email',
                    'data.attributes.password' => 'required|string|min:6|confirmed',
                ],
                'update' => [
                    'data.attributes.name' => 'sometimes|string',
                    'data.attributes.profile_avatar' => 'sometimes|image|mimes:jpg,png,jpeg,svg',
                    'data.attributes.email' => 'sometimes|email|unique:users,email',
                    'data.attributes.username' => 'sometimes|string|unique:users,username',
                    'data.attributes.status' => 'sometimes|boolean',
                ],
            ],
            'relationships' => [
                [
                    'type' => 'projects',
                    'method' => 'projects',
                ],
                [
                    'type' => 'projects',
                    'method' => 'invitations',
                ],
                [
                    'type' => 'tasks',
                    'method' => 'tasks',
                ],
                [
                    'type' => 'tasks',
                    'method' => 'tasksAssigned',
                ],
            ],
        ],
        'categories' => [
            'allowedSorts' => [
                'title',
                'created_at',
            ],
            'allowedIncludes' => [
                'tasks',
            ],
            'validationRules' => [
                'create' => [],
                'update' => [],
            ],
            'relationships' => [
                [
                    'type' => 'tasks',
                    'method' => 'tasks',
                ],
            ],
        ],
        'projects' => [
            'allowedSorts' => [
                'name',
                'created_at',
                'updated_at',
            ],
            'allowedIncludes' => [
                'invitees',
                'creator',
                'tasks',
            ],
            'validationRules' => [
                'create' => [
                    'data.attributes.name' => 'required|string|unique:projects,name',
                ],
                'update' => [
                    'data.attributes.name' => 'required|string|unique:projects,name',

                ],
            ],
            'relationships' => [
                [
                    'type' => 'users',
                    'method' => 'invitees',
                ],
                [
                    'type' => 'tasks',
                    'method' => 'tasks',
                ],
                [
                    'type' => 'users',
                    'method' => 'creator',
                ],
            ],
        ],
        'tasks' => [
            'allowedSorts' => [
                'title',
                'created_at',
                'updated_at',
            ],
            'allowedIncludes' => [
                'assignees',
                'creator',
                'project',
                'project.creator',
            ],
            'validationRules' => [
                'create' => [
                    'data.attributes.title' => 'required|string|unique:tasks,title',
                    'data.attributes.description' => 'string',
                    'data.relationships.project' => 'required',
                    'data.attributes.deadline' => 'date_format:Y-m-d',
                ],
                'update' => [
                    'data.attributes.title' => 'sometimes|required|string|unique:tasks,title',
                    'data.attributes.description' => 'sometimes|string',
                    'data.attributes.relationships' => 'sometimes|required|array',
                    'data.attributes.deadline' => 'sometimes|date_format:Y-m-d',
                ],

            ],
            'relationships' => [
                [
                    'type' => 'users',
                    'method' => 'assignees',
                ],
                [
                    'type' => 'users',
                    'method' => 'creator',
                ],
                [
                    'type' => 'projects',
                    'method' => 'project',
                ],

            ],
        ],
        'groups' => [
            'allowedSorts' => [
                'title',
                'created_at',
                'updated_at',
            ],
            'allowedIncludes' => [
                'tasks',
                'creator',
            ],
            'validationRules' => [
                'create' => [
                    'data.attributes.title' => 'required|string|unique:groups,title',
                    'data.relationships.project' => 'required',
                    'data.relationships.creator' => 'required',
                ],
                'update' => [
                    'data.attributes.title' => 'sometimes|required|string|unique:groups,title',
                    'data.attributes.relationships' => 'sometimes|required',
                ],
            ],
            'relationships' => [
                [
                    'type' => 'tasks',
                    'method' => 'tasks',
                ],
                [
                    'type' => 'users',
                    'method' => 'creator',
                ],
            ],
        ],
    ],

];
