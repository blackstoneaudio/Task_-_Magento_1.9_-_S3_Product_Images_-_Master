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
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, "meta_authors", array(
  'group' => 'Meta Information',
  'type' => 'text',
  'label' => 'Meta Authors',
  'input' => 'textarea',
  'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
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
$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, "meta_narrators", array(
  'group' => 'Meta Information',
  'type' => 'text',
  'label' => 'Meta Narrators',
  'input' => 'textarea',
  'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
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