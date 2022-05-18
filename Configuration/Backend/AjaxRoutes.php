<?php

return [
    'mailcatcher_html' => [
        'path' => '/mailcatcher/html',
        'target' => \Xima\XmMailCatcher\Controller\AjaxController   ::class . '::loadHtmlAction',
    ],
];