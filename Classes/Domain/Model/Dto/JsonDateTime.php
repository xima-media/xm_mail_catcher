<?php

namespace Xima\XmMailCatcher\Domain\Model\Dto;

class JsonDateTime extends \DateTime implements \JsonSerializable
{
    public function jsonSerialize(): string
    {
        return $this->format('c');
    }
}
