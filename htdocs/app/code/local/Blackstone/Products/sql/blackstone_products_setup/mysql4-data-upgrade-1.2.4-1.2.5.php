<?php
/**
 * Created by PhpStorm.
 * User: erik.petterson
 * Date: 11/11/2015
 * Time: 9:39 AM
 */
/* @var $installer Mage_Catalog_Model_Resource_Setup */

$attr = array (
 'group' => 'General',
 'attribute_set' => 'Title',
 'attribute_model' => NULL,
 'backend' => 'eav/entity_attribute_backend_array',
 'type' => 'varchar',
 'table' => '',
 'frontend' => '',
 'input' => 'multiselect',
 'label' => 'Narrators',
 'frontend_class' => '',
 'source' => 'blackstone_people/attribute_person_source_type',
 'required' => '0',
 'user_defined' => '1',
 'default' => '',
 'unique' => '0',
 'note' => '',
 'input_renderer' => NULL,
 'global' => '1',
 'visible' => '1',
 'searchable' => '1',
 'filterable' => '1',
 'comparable' => '1',
 'visible_on_front' => TRUE,
 'is_html_allowed_on_front' => '0',
 'is_used_for_price_rules' => '1',
 'filterable_in_search' => '1',
 'used_in_product_listing' => TRUE,
 'used_for_sort_by' => '0',
 'is_configurable' => '1',
 'apply_to' => 'grouped',
 'visible_in_advanced_search' => '1',
 'position' => '1',
 'wysiwyg_enabled' => '0',
 'used_for_promo_rules' => '1',
);
$this->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'narrators', $attr);