<?php

return [
    'options' => [
        /*
         * Путь к каталогу, который надо сканировать
         * Должен всегдна начинаться с /mamba
         */
        'source' => '/mamba/modules/uni-comments',
        /*
         * Маска файлов для сканирования (не менять)
         */
        'filePattern' => '*.php',
        /*
         * Файл графа (не менять)
         */
        'target' => '/app/phpda.svg',
        /*
         * Форматирование нэймспейсов
         *
         * Mamba\Comments\Application\Command
         * При offset=0 и length=2 получим - Mamba\Comments
         * Соответсвенно все правила в группах нужно будет писать исходя, что в твоем распоряжении будет
         * два первых элемента нэйспейса. Если надо больше, то увеличивай длинну и меняй правила.
         */
        'ns.slice.offset' => 0,
        'ns.slice.length' => 2
    ],
    /*
     * Конфигурация цвета фона и тега. Если не указать, то будет использоваться дефолтный фон.
     */
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
    /*
     * Конфигурация групп, фильтров и тегов
     */
    'tagger_groups' => [
        /*
         * Правила фильтрации
         */
        'filter' => [
            /*
             * Список тегов, по которым надо отфильтровать зависимости
             */
            'use_tags' => [],
            /*
             * Пространсва имен, которые НЕ нужно фильтровать.
             * Если попробовать посмотреть только инфраструктурные зависимости и не смотреть бизнесовые,
             * то тогда граф не построится, т.к. бизнесовые зависимсоти не попадут в фильт и вершин у графа не будет.
             *
             * Это совего родо белый список для фильтра.
             */
            'root_names' => [
                'Mamba\Comments'
            ]
        ],
        /*
         * Группы и теги
         */
        'items' => [
            [
                /*
                 * Название группы в графе
                 */
                'title' => 'Mamba common',
                /*
                 * Тег группы
                 */
                'tag' => 'common',
                /*
                 * Элементы группы (пространства имен)
                 */
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
    ]
];
