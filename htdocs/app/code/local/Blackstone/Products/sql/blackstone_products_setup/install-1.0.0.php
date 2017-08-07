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
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, "release_date", array(
  'group' => 'General',
  'type' => 'datetime',
  'label' => 'Release Date',
  'input' => 'date',
  'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
  'visible' => TRUE,
  'required' => FALSE,
  'user_defined' => TRUE,
  'default' => "",
  'searchable' => FALSE,
  'filterable' => FALSE,
  'comparable' => FALSE,
  'visible_on_front' => TRUE,
  'unique' => FALSE,
 )
);
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, "catalog_pid", array(
  'group' => 'General',
  'type' => 'int',
  'label' => 'Catalog PID',
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
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, "image_override_150", array(
  'group' => 'Images',
  'type' => 'varchar',
  'label' => 'Image Override 150',
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
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, "image_override_350", array(
  'group' => 'Images',
  'type' => 'varchar',
  'label' => 'Image Override 350',
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
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, "image_override_800", array(
  'group' => 'Images',
  'type' => 'varchar',
  'label' => 'Image Override 800',
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