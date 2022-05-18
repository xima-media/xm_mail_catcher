<?php

namespace Xima\XmMailCatcher\Domain\Model\Dto;

class MailMessage
{
    public string $messageId = '';

    public ?\DateTime $date = null;

    public string $subject = '';

    public string $from = '';

    public string $fromName = '';

    public string $to = '';

    public string $toName = '';

    public string $bodyPlain = '';

    public string $bodyHtml = '';

    public function getFileName(): string
    {
        $name = hash('md5', $this->messageId);

        if ($this->date) {
            $name = $this->date->getTimestamp() . '-' . $name . '.json';
        }

        return $name;
    }

    public function loadFromJson(array $data): void
    {
        foreach ($data as $key => $value) {
            if ($key === 'date') {
                $this->date = new \DateTime($value);
                continue;
            }
            $this->{$key} = $value;
        }
    }
}