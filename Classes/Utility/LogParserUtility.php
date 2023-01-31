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

        // decode whole file
        $this->fileContent = quoted_printable_decode($this->fileContent);

        preg_match_all(
            '/(?:; boundary=)(.+)(?:\r\n)/Ums',
            $this->fileContent,
            $boundaries
        );

        if (!isset($boundaries[1])) {
            return;
        }

        foreach ($boundaries[1] as $boundary) {
            $separator = '--' . $boundary . '--';
            $messageParts = explode($separator, $this->fileContent);
            $messageString = $messageParts[0];
            $this->fileContent = $messageParts[1];
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

        preg_match('/(?:(?:^From:\s)(?:.*\<)(.*)(?:\>\n))|(?:(?:^From:\s)(.*)(?:\r\n))/m', $msg, $from);
        if (isset($from[1])) {
            $dto->from = array_values(array_filter($from))[1];
        }

        preg_match('/(?:^To:\s)(.*)(?:\s\<)/m', $msg, $toName);
        if (isset($toName[1])) {
            $dto->toName = $toName[1];
        }

        preg_match('/(?:(?:^To:\s)(?:.*\<)(.*)(?:\>\n))|(?:(?:^To:\s)(.*)(?:\r\n))/m', $msg, $to);
        if (isset($to[1])) {
            $dto->to = array_values(array_filter($to))[1];
        }

        preg_match('/(?:^Subject:\s)(.*)(?:\r\n)/m', $msg, $subject);
        if (isset($subject[1])) {
            $dto->subject = $subject[1];
        }

        preg_match('/(?:^Message-ID:\s\<)(.*)(?:\>\r\n)/m', $msg, $messageId);
        if (isset($messageId[1])) {
            $dto->messageId = $messageId[1];
        }

        preg_match('/(?:^Date:\s)(.*)(?:\r\n)/m', $msg, $date);
        if (isset($date[1])) {
            try {
                $date = new JsonDateTime($date[1]);
                $dto->date = $date;
            } catch (\Exception $e) {
            }
        }

        preg_match_all('/(?:boundary\=)(.*)(?:\r\n)/Ums', $msg, $boundaries);
        if (!isset($boundaries[1])) {
            return $dto;
        }

        $messageParts = explode('--' . $boundary[1], $msg);
        foreach ($messageParts as $part) {
            if (strpos($part, 'Content-Type: text/plain')) {
                $dto->bodyPlain = self::removeFirstThreeLines($part);
            }
            if (strpos($part, 'Content-Type: text/html')) {
                $dto->bodyHtml = self::removeFirstThreeLines($part);
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
        $tempPath = Environment::getPublicPath() . '/typo3temp/xm_mail_catcher/';

        if (!is_dir($tempPath)) {
            mkdir($tempPath);
        }

        return $tempPath;
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
