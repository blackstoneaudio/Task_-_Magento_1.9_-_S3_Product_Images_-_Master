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
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, "acumen_royalty_id", array(
  'group' => 'General',
  'type' => 'varchar',
  'label' => 'Acumen Royalty ID',
  'input' => 'text',
  'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
  'visible' => TRUE,
  'required' => FALSE,
  'user_defined' => TRUE,
  'default' => "",
  'searchable' => FALSE,
  'filterable' => FALSE,
  'comparable' => FALSE,
  'visible_on_front' => FALSE,
  'unique' => FALSE,
 )
);
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, "label", array(
  'group' => 'General',
  'type' => 'varchar',
  'label' => 'Label',
  'input' => 'text',
  'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
  'visible' => TRUE,
  'required' => FALSE,
  'user_defined' => TRUE,
  'default' => "",
  'searchable' => FALSE,
  'filterable' => FALSE,
  'comparable' => FALSE,
  'visible_on_front' => FALSE,
  'unique' => FALSE,
 )
);
$installer->endSetup();