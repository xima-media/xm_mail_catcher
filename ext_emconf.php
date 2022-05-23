<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Mail Catcher',
    'description' => 'Display mails that were send to log file',
    'category' => 'plugin',
    'author' => 'Maik Schneider',
    'author_email' => 'maik.scheider@xima.de',
    'author_company' => 'XIMA MEDIA GmbH',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '10.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.0.0-9.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
