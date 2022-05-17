<?php

namespace Xima\XmMailCatcher\Domain\Model\Dto;

class MailMessage
{
        public string $messageId = '';

        public ?\DateTime $date = null;

        public string $subject = '';
        public string $from = '';
        public string $to = '';
        public string $contentType = '';
        public string $body = '';


}