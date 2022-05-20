<?php

return [
    'mailcatcher_html' => [
        'path' => '/mailcatcher/html',
        'target' => \Xima\XmMailCatcher\Controller\AjaxController::class . '::loadHtmlAction',
    ],
    'mailcatcher_delete' => [
        'path' => '/mailcatcher/delete',
        'target' => \Xima\XmMailCatcher\Controller\AjaxController::class . '::deleteAction',
    ],
    'mailcatcher_delete_all' => [
        'path' => '/mailcatcher/delete/all',
        'target' => \Xima\XmMailCatcher\Controller\AjaxController::class . '::deleteAllAction',
    ],
];
