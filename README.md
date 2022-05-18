# Mail Catcher

This TYPO3 extension adds a module to view emails that were printed to file.

## Installation

```
composer require xima/xm-mail-catcher
```

## Configuration

To prevent TYPO3 from sending emails, start a fake SMTP server on the host machine via python:

```
nohup python -u -m smtpd -n -c DebuggingServer localhost:2500 >> var/www/html/var/log/mail.log &
```

Configure TYPO3 to use the local SMTP:

```
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport'] = 'smtp'
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_smtp_server'] = 'localhost:2500'
```