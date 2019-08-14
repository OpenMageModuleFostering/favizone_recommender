<?php
/**
* This upgrade script will run when upgrading from 1.1.0 to 1.0.3 or above.
 *
 * Deletes all existing module configurations.
 * Creates Registry Type Table
 *
 * @var Favizone_Recommender_Model_Resource_Setup $installer
 */
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$installer->getConnection()->delete(
    $installer->getTable('core/config_data'),
    '`path` LIKE "favizone_recommender/%"'
);

$tableName = $installer->getTable('favizone_recommender/accessKey');
if ($installer->getConnection()->isTableExists($tableName) == true) {

    $installer->getConnection()->dropTable($tableName);
}

$table = $installer->getConnection()
    ->newTable($tableName)
    ->addColumn('access_key_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true)
    )
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
            'nullable'  => false)
    )
    ->addColumn('access_key', Varien_Db_Ddl_Table::TYPE_TEXT, 25, array(
        'nullable'  => true)
    )
    ->addColumn('ab_test', Varien_Db_Ddl_Table::TYPE_TEXT, 25, array(
        'nullable'  => true)
    )
    ->addColumn('ab_diff', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'nullable'  => true,)
    )

    ->setComment('Favizone Recommender Table');
$installer->getConnection()->createTable($table);
$installer->endSetup();