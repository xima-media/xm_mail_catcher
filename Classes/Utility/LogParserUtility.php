<?php

namespace Xima\XmMailCatcher\Utility;

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Xima\XmMailCatcher\Domain\Model\Dto\JsonDateTime;
use Xima\XmMailCatcher\Domain\Model\Dto\MailAttachment;
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
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
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
            '/(?:; boundary=)(.+)(?:\r\n)/Ums',
            $this->fileContent,
            $boundaries
        );

        // decode whole file
        $this->fileContent = quoted_printable_decode($this->fileContent);

        if (!isset($boundaries[1])) {
            return;
        }

        foreach ($boundaries[1] as $boundary) {
            $separator = '--' . $boundary . '--';
            $messageParts = explode($separator, $this->fileContent);

            if (strpos($messageParts[0], 'boundary=') === false) {
                continue;
            }

            $messageString = $messageParts[0];
            $this->fileContent = $messageParts[1] ?? '';
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

        foreach ($boundaries[1] as $boundary) {
            $messageParts = explode('--' . $boundary, $msg);
            foreach ($messageParts as $part) {
                if (strpos($part, 'Content-Type: text/plain')) {
                    $dto->bodyPlain = self::removeLinesFromStart($part, 3);
                }
                if (strpos($part, 'Content-Type: text/html')) {
                    $dto->bodyHtml = self::removeLinesFromStart($part, 3);
                }
                if (strpos($part, 'Content-Type: application/') || strpos($part, 'Content-Type: image/')) {
                    self::createFile($part, $dto);
                }
            }
        }

        return $dto;
    }

    protected static function createFile(string $messagePart, MailMessage $dto): void
    {
        try {
            preg_match('/(?:filename=)(.+)(?:\r\n)/', $messagePart, $filenameParts);
            $filename = $filenameParts[1];

            $folder = self::getTempPath() . $dto->messageId;
            if (!file_exists($folder)) {
                mkdir($folder);
            }

            $filepath = $folder . '/' . $filename;
            $data = self::removeLinesFromStart($messagePart, 5);
            $data = str_replace(['\r', '\n'], '', $data);
            $file = base64_decode($data, true);

            if (!$file) {
                return;
            }

            file_put_contents($filepath, $file);
            $size = filesize($filepath) ?: 0;

            $mailAttachment = new MailAttachment();
            $mailAttachment->filename = $filename;
            $mailAttachment->filesize = $size;
            $mailAttachment->publicPath = self::getPublicPath() . $dto->messageId . '/' . $filename;

            $dto->attachments[] = $mailAttachment;
        } catch (\Exception $e) {
        }
    }

    public static function removeLinesFromStart(string $string, int $lineCount): string
    {
        return implode(PHP_EOL, array_slice(explode(PHP_EOL, $string), ($lineCount + 1)));
    }

    public static function getPublicPath(): string
    {
        return '/typo3temp/xm_mail_catcher/';
    }

    public static function getTempPath(): string
    {
        $tempPath = Environment::getPublicPath() . self::getPublicPath();

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
