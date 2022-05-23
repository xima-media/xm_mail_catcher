<?php

namespace Xima\XmMailCatcher\Domain\Model\Dto;

class MailMessage
{
    /**
     * @var string
     */
    public $messageId = '';

    /**
     * @var null|\DateTime
     */
    public $date = null;

    /**
     * @var string
     */
    public $subject = '';

    /**
     * @var string
     */
    public $from = '';

    /**
     * @var string
     */
    public $fromName = '';

    /**
     * @var string
     */
    public $to = '';

    /**
     * @var string
     */
    public $toName = '';

    /**
     * @var string
     */
    public $bodyPlain = '';

    /**
     * @var string
     */
    public $bodyHtml = '';

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

    public function getDisplayFromAddress(): string
    {
        if ($this->fromName) {
            return $this->fromName . ' <' . $this->from . '>';
        }
        return $this->from;
    }

    public function getDisplayToAddress(): string
    {
        if ($this->toName) {
            return $this->toName . ' <' . $this->to . '>';
        }
        return $this->to;
    }
}
