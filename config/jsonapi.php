<?php

return  [
    'resources' => [
        'users' => [
            'allowedSorts' => [
                'name',
                'created_at',
                'updated_at'
            ],
            'validationRules' => [
                'create' => [
                    'data.attributes.name' => 'required|string',
                    'data.attributes.email' => 'required|string|email|unique:users,email',
                    'data.attributes.password' => 'required|string|min:6|confirmed'
                ],
                'update' => [
                    'data.attributes.name' => 'sometimes|string',
                    'data.attributes.profile_avatar' => 'sometimes|image|mimes:jpg,png,jpeg,svg',
                    'data.attributes.email' => 'sometimes|email|unique:users,email',
                    'data.attributes.username' => 'sometimes|string|unique:users,username',
                    'data.attributes.status' => 'sometimes|boolean',
                ]
            ],

        ],
        'categories' => [
            'allowedSorts' => [
                'created_at',
                'updated_at'
            ],
            'validationRules' => [
                'create' => [
                   
                ],
                'update' => [
                    
                ]
            ],
        ],
        'projects' => [
            'allowedSorts' => [
                'name',
                'created_at',
                'updated_at'
            ],
            'allowedIncludes' => [
                'invitees',
            ],
            'validationRules' => [
                'create' => [
                    'data.attributes.name' => 'required|string|unique:projects,name',
                ],
                'update' => [
                    'data.attributes.name' => 'sometimes|string',
                    'data.attributes.profile_avatar' => 'sometimes|image|mimes:jpg,png,jpeg,svg',
                    'data.attributes.email' => 'sometimes|email|unique:users,email',
                    'data.attributes.username' => 'sometimes|string|unique:users,username',
                    'data.attributes.status' => 'sometimes|boolean',
                ]
            ],
            'relationships' => [
                [
                    'type' => 'users',
                    'method' => 'invitees',
                ]
            ]
        ],
        'projects' => [
            'allowedSorts' => [
                'title',
                'created_at',
                'updated_at'
            ],
            'allowedIncludes' => [
            ],
            'validationRules' => [
                'create' => [
                    'data.attributes.name' => 'required|string|unique:projects,name',
                ],
                'update' => [
                    'data.attributes.name' => 'sometimes|string',
                    'data.attributes.profile_avatar' => 'sometimes|image|mimes:jpg,png,jpeg,svg',
                    'data.attributes.email' => 'sometimes|email|unique:users,email',
                    'data.attributes.username' => 'sometimes|string|unique:users,username',
                    'data.attributes.status' => 'sometimes|boolean',
                ]
            ],
            'relationships' => [
                
            ]
        ],
    ]

];
