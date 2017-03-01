<?php
/**
 * Modified from:
 * http://magento.stackexchange.com/a/116987/39367
 * https://github.com/magento/magento2/blob/develop/dev/tests/unit/framework/autoload.php
 *
 * This class lets us mock generated magento files.
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
$autoloader = new \Magento\Framework\TestFramework\Unit\Autoloader\ExtensionGeneratorAutoloader(
    new \Magento\Framework\Code\Generator\Io(
        new \Magento\Framework\Filesystem\Driver\File(),
        'phpUnitGeneratedFiles/'
    )
);
spl_autoload_register([$autoloader, 'load']);
