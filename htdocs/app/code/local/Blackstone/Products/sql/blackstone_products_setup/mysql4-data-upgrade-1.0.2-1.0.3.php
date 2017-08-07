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
$sNewSetName = 'Title';
$iCatalogProductEntityTypeId = (int)$installer->getEntityTypeId('catalog_product');

$oAttributeset = Mage::getModel('eav/entity_attribute_set')
 ->setEntityTypeId($iCatalogProductEntityTypeId)
 ->setAttributeSetName($sNewSetName);

if($oAttributeset->validate()) {
  $oAttributeset
   ->save()
   ->initFromSkeleton($iCatalogProductEntityTypeId)
   ->save();
}

$installer->endSetup();