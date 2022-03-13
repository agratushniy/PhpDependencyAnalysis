<?php

return [
    'options' => [
        'source' => '/mamba/modules/uni-comments',
        'filePattern' => '*.php',
        'target' => '/app/phpda.svg'
    ],
    'tags_colors' => [
        [
            'name' => 'business',
            'color' => '#Ead8ad'
        ],
        [
            'name' => 'infrastructure',
            'color' => '#D1eaad'
        ]
    ],
    'tagger_groups' => [
        'filter' => [
            'use_tags' => [],
            'root_names' => [
                'Mamba\Comments'
            ]
        ],
        'items' => [
            [
                'title' => 'Mamba common',
                'tag' => 'common',
                'items' => [
                    'Mamba\Context',
                    'Mamba\CommandBus'
                ]
            ],
            [
                'title' => 'Comments',
                'tag' => 'business',
                'items' => [
                    'Mamba\Comments'
                ]
            ],
            [
                'title' => 'Hitlist',
                'tag' => 'business',
                'items' => [
                    'Hitlist'
                ]
            ],
            [
                'title' => 'Anketa',
                'tag' => 'business',
                'items' => [
                    'Anketa'
                ]
            ],
            [
                'title' => 'Symfony',
                'tag' => 'infrastructure',
                'items' => [
                    'Symfony'
                ]
            ],
            [
                'title' => 'Mamba infrastructure',
                'tag' => 'infrastructure',
                'items' => [
                    'RabbitMQ'
                ]
            ]
        ]
    ],
    'filter.include' => [
        /*'Mamba\Comments',
        'Hitlist',
        'Anketa',
        'Symfony'*/
    ],
    'filter.tags.root_nodes' => [
        'Mamba\Comments',
    ],
    'filter.tags.supported' => [
        'framework', 'infrastructure'
    ],
    'filter.tags.collection' => [
        'business' => [
            'Mamba\Comments',
            'Hitlist',
            'Anketa'
        ],
        'framework' => [
            'Symfony'
        ],
        'infrastructure' => [
            'RabbitMQ'
        ]
    ]
];
