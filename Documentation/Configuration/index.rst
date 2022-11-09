Configuration
-------------

To prevent TYPO3 from sending emails, change the transport to **mbox** (`Mail-API <https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/Mail/Index.html>`__). 
This way TYPO3 writes the outgoing emails to a log file that you can specify via `transport_mbox_file`. The path musst be absolute.

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport'] = 'mbox'
    $GLOBALS['TYPO3_CONF_VARS']['MAIL']['transport_mbox_file'] = \TYPO3\CMS\Core\Core\Environment::getProjectPath() . '/var/log/mail.log'

In the configuration of this extension, adjust the path to the one, you just selected. This path musst be relative.

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['xm_mail_catcher']['logPath'] = '/var/log/mail.log'