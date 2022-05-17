<?php

defined('TYPO3_MODE') || die();

call_user_func(
    static function () {

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup('@import "EXT:xm_mail_catcher/Configuration/TypoScript/setup.typoscript"');

        /**
         * Register icon
         */
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
            'tx_mailcatcher',
            'bottom',
            [
                \Xima\XmMailCatcher\Controller\BackendController::class => 'index'
            ],
            [
                'access' => 'admin',
                'iconIdentifier' => 'module-mailcatcher',
                'labels' => 'LLL:EXT:xm_mail_catcher/Resources/Private/Language/locallang_mod.xlf',
                'navigationComponentId' => '',
                'inheritNavigationComponentFromMainModule' => false,
            ]
        );

    }
);