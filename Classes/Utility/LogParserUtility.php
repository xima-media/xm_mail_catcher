<?php

namespace Xima\XmMailCatcher\Utility;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Xima\XmMailCatcher\Domain\Model\Dto\JsonDateTime;
use Xima\XmMailCatcher\Domain\Model\Dto\MailMessage;

class LogParserUtility
{
    protected string $fileContent = '';

    /**
     * @var array<MailMessage>
     */
    protected array $messages = [];

    protected function loadLogFile(): void
    {
        /** @var ExtensionConfiguration $extensionConfiguration */
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $logPath = $extensionConfiguration->get('xm_mail_catcher', 'logPath');
        $absolutePath = Environment::getProjectPath() . $logPath;

        if (!file_exists($absolutePath)) {
            return;
        }

        $this->fileContent = (string)file_get_contents($absolutePath);
    }

    /**
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     */
    protected function emptyLogFile(): void
    {
        /** @var ExtensionConfiguration $extensionConfiguration */
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $logPath = $extensionConfiguration->get('xm_mail_catcher', 'logPath');
        $absolutePath = Environment::getProjectPath() . $logPath;

        if (!file_exists($absolutePath)) {
            return;
        }

        file_put_contents($absolutePath, '');
    }

    protected function extractMessages(): void
    {
        if (!$this->fileContent) {
            return;
        }

        preg_match_all(
            '/(?:---------- MESSAGE FOLLOWS ----------\n)(.*)(?:------------ END MESSAGE ------------)+/Ums',
            $this->fileContent,
            $messages
        );

        if (!isset($messages[1])) {
            return;
        }

        foreach ($messages[1] as $messageString) {
            // remove line breaks that cut strings
            $messageString = preg_replace("/\=(\'|\")\nb(\'|\")/Ums", '', (string)$messageString);
            // remove b' '
            $messageString = preg_replace("/^b(\'|\")(.*)(\'|\")$/Ums", '$2', (string)$messageString);
            // convert to object
            $this->messages[] = self::convertToDto((string)$messageString);
        }
    }

    protected function writeMessagesToFile(): void
    {
        foreach ($this->messages as $message) {
            $fileContent = (string)json_encode($message);
            $fileName = $message->getFileName();
            $filePath = self::getTempPath() . $fileName;
            GeneralUtility::writeFileToTypo3tempDir($filePath, $fileContent);
        }
    }

    protected static function convertToDto(string $msg): MailMessage
    {
        $dto = new MailMessage();

        preg_match('/(?:^From:\s)(.*)(?:\s\<)/m', $msg, $fromName);
        if (isset($fromName[1])) {
            $dto->fromName = $fromName[1];
        }

        preg_match('/(?:(?:^From:\s)(?:.*\<)(.*)(?:\>\n))|(?:(?:^From:\s)(.*)(?:\n))/m', $msg, $from);
        if (isset($from[1])) {
            $dto->from = array_values(array_filter($from))[1];
        }

        preg_match('/(?:^To:\s)(.*)(?:\s\<)/m', $msg, $toName);
        if (isset($toName[1])) {
            $dto->toName = $toName[1];
        }

        preg_match('/(?:(?:^To:\s)(?:.*\<)(.*)(?:\>\n))|(?:(?:^To:\s)(.*)(?:\n))/m', $msg, $to);
        if (isset($to[1])) {
            $dto->to = array_values(array_filter($to))[1];
        }

        preg_match('/(?:^Subject:\s)(.*)(?:\n)/m', $msg, $subject);
        if (isset($subject[1])) {
            $dto->subject = $subject[1];
        }

        preg_match('/(?:^Message-ID:\s\<)(.*)(?:\>\n)/m', $msg, $messageId);
        if (isset($messageId[1])) {
            $dto->messageId = $messageId[1];
        }

        preg_match('/(?:^Date:\s)(.*)(?:\n)/m', $msg, $date);
        if (isset($date[1])) {
            try {
                $date = new JsonDateTime($date[1]);
                $dto->date = $date;
            } catch (\Exception $e) {
            }
        }

        preg_match('/(?:boundary\=)(.*)(?:\n)/m', $msg, $boundary);
        if (!isset($boundary[1])) {
            return $dto;
        }

        $messageParts = explode('--' . $boundary[1], $msg);
        foreach ($messageParts as $part) {
            if (strpos($part, 'Content-Type: text/plain')) {
                $body = self::removeFirstThreeLines($part);
                $dto->bodyPlain = quoted_printable_decode($body);
            }
            if (strpos($part, 'Content-Type: text/html')) {
                $body = self::removeFirstThreeLines($part);
                $dto->bodyHtml = quoted_printable_decode($body);
            }
        }

        return $dto;
    }

    public static function removeFirstThreeLines(string $string): string
    {
        return implode(PHP_EOL, array_slice(explode(PHP_EOL, $string), 4));
    }

    public static function getTempPath(): string
    {
        return Environment::getPublicPath() . '/typo3temp/xm_mail_catcher/';
    }

    public function run(): void
    {
        $this->loadLogFile();
        $this->extractMessages();
        $this->writeMessagesToFile();
        $this->emptyLogFile();
    }

    public function loadMessages(): void
    {
        $messageFiles = array_filter((array)scandir(self::getTempPath()), function ($filename) {
            return strpos((string)$filename, '.json');
        });

        $this->messages = [];

        foreach ($messageFiles as $filename) {
            if ($message = $this->getMessageByFilename((string)$filename)) {
                $this->messages[] = $message;
            }
        }
    }

    /**
     * @return \Xima\XmMailCatcher\Domain\Model\Dto\MailMessage[]
     */
    public function getMessages(): array
    {
        $this->loadMessages();
        return $this->messages;
    }

    public function getMessageByFilename(string $filename): ?MailMessage
    {
        $file = self::getTempPath() . '/' . $filename;

        if (!file_exists($file)) {
            return null;
        }

        $fileContent = file_get_contents(self::getTempPath() . '/' . $filename);
        $data = json_decode((string)$fileContent, true);
        $message = new MailMessage();
        $message->loadFromJson($data);

        return $message;
    }

    public function deleteMessageByFilename(string $filename): bool
    {
        $file = self::getTempPath() . '/' . $filename;

        if (!file_exists($file)) {
            return false;
        }

        return unlink($file);
    }

    public function deleteMessages(): bool
    {
        $success = true;

        $messageFiles = array_filter((array)scandir(self::getTempPath()), function ($filename) {
            return strpos((string)$filename, '.json');
        });

        foreach ($messageFiles as $filename) {
            $success = $this->deleteMessageByFilename((string)$filename);
            if (!$success) {
                break;
            }
        }

        return $success;
    }
}
