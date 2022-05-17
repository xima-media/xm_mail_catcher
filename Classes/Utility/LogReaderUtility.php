<?php

namespace Xima\XmMailCatcher\Utility;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use Xima\XmMailCatcher\Domain\Model\Dto\MailMessage;

class LogReaderUtility
{

    /**
     * @return array<MailMessage>
     */
    public function getMails(): array
    {
        $parser = GeneralUtility::makeInstance(LogParserUtility::class);
        $parser->run();

        return [];
    }
}