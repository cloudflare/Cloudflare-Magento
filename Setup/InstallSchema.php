<?php

namespace CloudFlare\Plugin\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    const CLOUDFLARE_DATA_TABLE_NAME = "cloudflare_data";
    const CLOUDFLARE_DATA_TABLE_ID_COLUMN = "id";
    const CLOUDFLARE_DATA_TABLE_KEY_COLUMN = "key";
    const CLOUDFLARE_DATA_TABLE_VALUE_COLUMN = "value";

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $tableName = $installer->getTable(self::CLOUDFLARE_DATA_TABLE_NAME);

        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    self::CLOUDFLARE_DATA_TABLE_ID_COLUMN,
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    self::CLOUDFLARE_DATA_TABLE_KEY_COLUMN,
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Key'
                )
                ->addColumn(
                    self::CLOUDFLARE_DATA_TABLE_VALUE_COLUMN,
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Value'
                )
                ->setComment('CloudFlare Key/Value Store.')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
