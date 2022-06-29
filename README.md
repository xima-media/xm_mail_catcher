# Mail Catcher

This TYPO3 extension adds a module to view emails that were printed to file.

**Requirements**: Python & 1 free Port

![backend_module](Documentation/example_backend_module.jpg)

## Installation

```
composer require xima/xm-mail-catcher
```

## Configuration

To prevent TYPO3 from sending emails, change the transport to `mbox` ([Mail-API](https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/Mail/Index.html)). This way TYPO3 writes the outgoing emails to a log file that you can specify via `transport_mbox_file`. The path musst be absolute.

```
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport'] = 'mbox';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_mbox_file'] = \TYPO3\CMS\Core\Core\Environment::getProjectPath() . '/var/log/mail.log';
```

In the configuration of this extension, adjust the path to the one, you just selected. This path musst be relative.

```
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['xm_mail_catcher']['logPath'] = '/var/log/mail.log'
```
