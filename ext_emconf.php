<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Mail Catcher',
    'description' => 'Display mails that were send to log file',
    'category' => 'plugin',
    'author' => 'Maik Schneider',
    'author_email' => 'mas@xima.de',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.0.0-11.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];