<?php
/**
 * Created by PhpStorm.
 * User: erik.petterson
 * Date: 11/11/2015
 * Time: 9:39 AM
 */
/* @var $installer Mage_Catalog_Model_Resource_Setup */

$installer = $this;
$installer->startSetup();
$installer->getConnection();
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, "release_date_unix", array(
  'group' => 'General',
  'type' => 'varchar',
  'label' => 'Release Date Unix',
  'input' => 'text',
  'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
  'visible' => TRUE,
  'required' => FALSE,
  'user_defined' => TRUE,
  'default' => NULL,
  'searchable' => FALSE,
  'filterable' => FALSE,
  'comparable' => FALSE,
  'visible_on_front' => TRUE,
  'unique' => FALSE,
  'used_in_product_listing' => TRUE,
 )
);
$installer->endSetup();