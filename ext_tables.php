<?php

declare(strict_types=1);

$iconsToRegistser = [
    'module-mailcatcher'
];
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
foreach ($iconsToRegistser as $iconName) {
    $iconRegistry->registerIcon(
        $iconName,
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:xm_mail_catcher/Resources/Public/Images/' . $iconName . '.svg']
    );
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'XmMailCatcher',
    'system',
    'mails',
    '',
    [
        \Xima\XmMailCatcher\Controller\BackendController::class => 'index'
    ],
    [
        'access' => 'admin',
        'iconIdentifier' => 'module-mailcatcher',
        'labels' => 'LLL:EXT:xm_mail_catcher/Resources/Private/Language/locallang_mod.xlf',
    ]
);
