<?php

namespace Xima\XmMailCatcher\Utility;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Xima\XmMailCatcher\Domain\Model\Dto\MailMessage;

class LogParserUtility
{

    protected string $fileContent = '';

    protected function loadLogFile(): void
    {
        $logPath = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('xm_mail_catcher', 'logPath');
        $absolutePath = Environment::getProjectPath() . $logPath;

        if (!file_exists($absolutePath)) {
            return;
        }

        $this->fileContent = file_get_contents($absolutePath);
    }

    protected function extractMessages(): void
    {
        if (!$this->fileContent) {
            return;
        }

        preg_match_all('/(?:---------- MESSAGE FOLLOWS ----------\n)(.*)(?:------------ END MESSAGE ------------)+/Ums', $this->fileContent, $messages);

        if (!isset($messages[1])) {
            return;
        }

        foreach($messages[1] as $messageString) {
            // remove line breaks that cut strings
            $messageString = preg_replace("/((\=(\'|\")\n)^b\'|\")/Ums", '', $messageString);
            // remove b' '
            $messageString = preg_replace("/(^b\'|\")(.*)(\'|\"$)/Ums", '$2', $messageString);

        }


        $e = '';
    }

    public function getTempPath(): string
    {
        return Environment::getPublicPath() . '/typo3temp/xm_mail_catcher/';
    }

    public function run(): void
    {
        $this->loadLogFile();
        $this->extractMessages();
    }
}