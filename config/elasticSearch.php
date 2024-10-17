<?php
/*
 * elasticsearch constants file
 */
return [

    'searchEngineUrl' => 'http://104.46.44.55:9200',
    'indexName' => 'olahub-php-test-live',
    'catalogItems' => 'catalogItems',
    'catalogSections' => 'catalogSections',
    'catalogItemTypes' => 'catalogItemTypes',
    'settingsAndMapping' => [
        'settings' => [
            'analysis' => [
                'analyzer' => [
                    "autocomplete" => [
                        "type" => "custom",
                        "tokenizer" => "standard",
                        "filter" => [
                            "lowercase",
                            "english_stemmer",
                            "arabic_stemmer",
                            "autocomplete_filter"
                        ]
                    ]
                ],
                'filter' => [
//                    "trigrams_filter" => [
//                        "type" => "ngram",
//                        "min_gram" => 1,
//                        "max_gram" => 1
//                    ],
                    "autocomplete_filter" => [
                        "type" => "edge_ngram",
                        "min_gram" => 1,
                        "max_gram" => 20
                    ],
                    'english_stemmer' => [
                        'type' => 'stemmer',
                        'language' => 'english'
                    ],
                    'arabic_stemmer' => [
                        'type' => 'stemmer',
                        'language' => 'arabic'
                    ]
                ]
            ]
        ],
        'mappings' => [
            '_default_' => [
                    '_all' => [
                        'enabled' => true,
                        'analyzer' => 'autocomplete',
                        "search_analyzer" => "autocomplete"
                    ]
                ]
            
        ]
    ]
];


