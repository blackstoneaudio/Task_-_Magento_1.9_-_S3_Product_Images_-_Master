<?php
/**
 * Blackstone_People extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category       Blackstone
 * @package        Blackstone_People
 * @copyright      Copyright (c) 2016
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 *
 * People importer helper
 *
 * Created by PhpStorm.
 * User: erik.petterson
 * Date: 9/29/2016
 * Time: 9:14 AM
 */

class Blackstone_Products_Helper_Importer extends Mage_Core_Helper_Abstract {

  protected $scope = false;

  protected $sku = false;

  protected $model = false;

  protected $modelData = false;

  protected $isNew = false;

  protected $jsonData = false;

  protected $typeData = false;

  protected $errors = array();

  protected $results = array();

  protected $isActive = false;

  protected $genreRootCat = 2041;

  protected $customRootCat = 2033;

  protected $genreCats = array();

  protected $allCats = array();

  protected $customCats = array();

  protected $websiteIds = array();

  protected $storeViews = array();

  protected $defaultSetId = 4;

  protected $titleSetId = 9;

  protected $pid = false;

  protected $labelSuffix = false;

  protected $productType = 'simple';

  protected $productGroup = false;

  protected $productGroupCodes = array();

  protected $productProviderCodes = array();

  protected $productImprintCodes = array();

  protected $productDealCodes = array();

  protected $links = array();

  protected $stockArray = array();

  protected $associationPosition = 999;

  protected $_disableUpdateOverride = FALSE; //allows for overriding the 'dont bother updating disabled products' logic

  /*  function __construct() {
      //create admin session so we can use protected product data methods
      Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
      Mage::getSingleton('core/session', array('name' => 'adminhtml'));
      $session = Mage::getSingleton('admin/session');
      $session->start();
    }*/

  public function process($jsonData, $sku, $scope) {
    Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
    $this->reset(); //reset
    //set globals
    $this->sku = $sku;
    $this->scope = $scope;
    $this->jsonData = $jsonData;

    $id = Mage::getModel('catalog/product')->getResource()->getIdBySku($sku);
    $this->model = Mage::getModel('catalog/product');
    if($id) {
      $this->model->load($id);
    } else {
      //new title, get empty model
      $this->isNew = true;
      $this->model->setCreatedAt(strtotime('now')); //product creation time
    }
    $this->model->setUpdatedAt(strtotime('now')); //product update time

    $this->getArrayOfWebsites();
    $this->model->setWebsiteIds($this->websiteIds);
    //$this->model->setStoreId($this->storeViews);

    //update some basic stuff only if it is active
    if($this->jsonData['status'] == 1 || $this->_disableUpdateOverride) {

      //find the type data
      if(isset($jsonData['type_data'])) {
        $this->typeData = $jsonData['type_data'];
      } else {
        $this->results['success'] = FALSE;
        $this->errors[] = array(
         'error' => 'missing type_data param',
         'location' => __METHOD__ . ' - L#' . __LINE__
        );
      }

      //set type specific data that must be done on the model
      switch($this->typeData['type']) {
        case ('title_product'):
          $this->model->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH);
          $this->model->setAttributeSetId($this->titleSetId);
          //no cats for title sub prods
          $catIds = array();
          break;
        default:
          $this->placeInCats();
          $this->model->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
          $this->model->setAttributeSetId($this->defaultSetId);
          break;
      }
      $this->model->setTaxClassId(2); // set as taxable goods

    }
    //load the model data
    $this->modelData = $this->model->getData();

    //check if its disabled
    if($this->jsonData['status'] != 1) {
      //disable
      $this->modelData['status'] = Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
      $this->isActive = false;
    }

    //not an else bcause we need an override
    if(($this->jsonData['status'] == 1) || $this->_disableUpdateOverride) {
      if($this->jsonData['status'] == 1) {
        //is active, set data
        //wrapped in double check because of override
        $this->isActive = true;
        $this->modelData['status'] = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
      }

      //do required
      if(isset($sku)) {
        $this->modelData['sku'] = $sku;
      } else {
        $this->results['success'] = FALSE;
        $this->errors[] = array(
         'error' => 'missing sku param',
         'location' => __METHOD__ . ' - L#' . __LINE__
        );
      }

      if(isset($jsonData['pid'])) {
        $this->modelData['catalog_pid'] = $jsonData['pid'];
        $this->pid = $jsonData['pid'];
        //get info from the PID value
        $this->getPIDInfo();
        $this->modelData['type_id'] = ($this->productType);
      } else {
        $this->results['success'] = FALSE;
        $this->errors[] = array(
         'error' => 'missing pid param',
         'location' => __METHOD__ . ' - L#' . __LINE__
        );
      }

      //set the product group from the pid
      if($this->pid) {
        $this->getFormatCodes();
        if(isset($this->productGroupCodes[$this->productGroup])) {
          $this->modelData['product_group'] = $this->productGroupCodes[$this->productGroup];
        } else {
          $this->results['success'] = FALSE;
          $this->errors[] = array(
           'error' => "PID translate label '".$this->productGroup."' does not match existing product_group attribute. Is this a new format?",
           'location' => __METHOD__ . ' - L#' . __LINE__
          );
        }
      }

      //set the provider
      $providerCodes = $this->getProviderCodes();
      if(isset($providerCodes[$jsonData['provider']])) {
        $this->modelData['provider'] = $providerCodes[$jsonData['provider']];
      } else {
        $this->errors[] = array(
         'error' => "Provider ID '".$jsonData['provider']."' does not match any existing provider ID attribute options. Is this a new provider?",
         'location' => __METHOD__ . ' - L#' . __LINE__
        );
      }

      //set the imprint
      $imprintCodes = $this->getImprintCodes();
      if(isset($imprintCodes[$jsonData['imprint']])) {
        $this->modelData['imprint'] = $imprintCodes[$jsonData['imprint']];
      } else {
        $this->errors[] = array(
         'error' => "Imprint ID '".$jsonData['imprint']."' does not match any existing imprint ID attribute options. Is this a new imprint?",
         'location' => __METHOD__ . ' - L#' . __LINE__
        );
      }

      //set the deal
      $dealCodes = $this->getDealCodes();
      if(isset($dealCodes[$jsonData['deal']])) {
        $this->modelData['deal'] = $dealCodes[$jsonData['deal']];
      } else {
        $this->errors[] = array(
         'error' => "Deal ID '".$jsonData['deal']."' does not match any existing deal ID attribute options. Is this a new deal?",
         'location' => __METHOD__ . ' - L#' . __LINE__
        );
      }

      if(isset($jsonData['name'])) {
        $this->modelData['name'] = $jsonData['name'] . $this->labelSuffix;
        $this->modelData['raw_name'] = $jsonData['name'];
      } else {
        $this->results['success'] = FALSE;
        $this->errors[] = array(
         'error' => 'missing name param',
         'location' => __METHOD__ . ' - L#' . __LINE__
        );
      }

      if(isset($jsonData['label'])) {
        $this->modelData['label'] = $jsonData['label'];
      } else {
        $this->results['success'] = FALSE;
        $this->errors[] = array(
         'error' => 'missing label param',
         'location' => __METHOD__ . ' - L#' . __LINE__
        );
      }

      if(isset($jsonData['description'])) {
        $this->modelData['description'] = $jsonData['description'];
      } else {
        $this->results['success'] = FALSE;
        $this->errors[] = array(
         'error' => 'missing description param',
         'location' => __METHOD__ . ' - L#' . __LINE__
        );
      }

      if(isset($jsonData['description'])) {
        $this->modelData['short_description'] = $jsonData['description'];
      } else {
        $this->results['success'] = FALSE;
        $this->errors[] = array(
         'error' => 'missing description param',
         'location' => __METHOD__ . ' - L#' . __LINE__
        );
      }

      if(isset($jsonData['weight'])) {
        $this->modelData['weight'] = $jsonData['weight'];
      } else {
        $this->results['success'] = FALSE;
        $this->errors[] = array(
         'error' => 'missing weight param',
         'location' => __METHOD__ . ' - L#' . __LINE__
        );
      }

      if(isset($jsonData['price'])) {
        $this->modelData['price'] = $jsonData['price'];
      } else {
        $this->results['success'] = FALSE;
        $this->errors[] = array(
         'error' => 'missing price param',
         'location' => __METHOD__ . ' - L#' . __LINE__
        );
      }

      //set non-required
      $this->modelData['credits'] = $jsonData['credits'];
      $this->modelData['msrp'] = $jsonData['msrp'];
      $this->modelData['release_date'] = $jsonData['release_date'];
      if(isset($jsonData['release_date']) && ($jsonData['release_date'])) {
        $this->modelData['release_date_unix'] = strtotime($jsonData['release_date']);
      } else {
        $this->modelData['release_date_unix'] = NULL;
      }
      $this->modelData['drm'] = $jsonData['drm'];
      $this->modelData['minimum_price'] = $jsonData['minimum_price'];
      $this->modelData['units'] = $jsonData['units'];
      $this->modelData['image_override_150'] = $jsonData['image_override_150'];
      $this->modelData['image_override_350'] = $jsonData['image_override_350'];
      $this->modelData['image_override_800'] = $jsonData['image_override_800'];
      $this->modelData['link_type'] = $this->typeData['link'];
      $this->modelData['link_id'] = $this->typeData['link_id'];
      $this->modelData['meta_description'] = $jsonData['description'];
      if(is_array($jsonData['territories'])) {
        $this->modelData['territories'] = strtolower(implode(',',$jsonData['territories']));
      } else {
        //set default to us and canada if nothing found.
        $this->modelData['territories'] = 'us,ca';
      }

      //set the algolia priority
      $this->modelData['algolia_uniqueness_priority'] = 100;


      //set type specific data
      switch($this->typeData['type']) {
        case ('title_product'):
          $this->setTitleData();
          $this->modelData['isbn_10'] = $this->typeData['isbn_10'];
          $this->modelData['isbn_13'] = $this->typeData['isbn_13'];
          $this->modelData['partner_ref'] = $this->typeData['partner_ref'];
          if(isset($this->typeData['rental_length'])) {
            $this->modelData['rental_length'] = $this->typeData['rental_length'];
          }
          if(isset($this->typeData['rental_grace_period'])) {
            $this->modelData['rental_grace_period'] = $this->typeData['rental_grace_period'];
          }
          //set the algolia uniqueness qualifier
          $this->modelData['algolia_uniqueness_group_id'] = $this->typeData['link_id'];
          break;
        default:
          //set the algolia uniqueness qualifier
          $this->modelData['algolia_uniqueness_group_id'] = $sku;
          $this->modelData['meta_keyword'] = implode(', ',$this->typeData['search_meta']['keywords']);
          break;
      }

      //get link specific relationships as an array
      //so we can set them after product save
      switch($this->typeData['link']) {
        case ('title'):
          $this->links[] = 'setTitleLink';
          break;
        default:
          $this->results['success'] = FALSE;
          $this->errors[] = array(
           'error' => "unknown type_data['link'] param value",
           'location' => __METHOD__ . ' - L#' . __LINE__
          );
          break;
      }

      //if required lets check if there is a parent product before we save this model
      if(in_array('setTitleLink',$this->links)) {
        $titleProdId = Mage::getModel('catalog/product')->getResource()->getIdBySku($this->typeData['link_id']);
        if(!$titleProdId) {
          //no parent product found..
          $this->errors[] = array(
           'error' => 'cannot save relation to title '.$this->typeData['link_id'].'. Title not found',
           'location' => __METHOD__ . ' - L#' . __LINE__
          );
          $this->results['success'] = FALSE;
        }
      }
    }

    //If no errors, and the book is active+not-new then save the book, do the stuff that has to be done after its saved, and save again..
    if((empty($this->errors)) && (!$this->isNew || ($this->isActive || $this->_disableUpdateOverride))) {
      try {
        //set data
        $this->model->setData($this->modelData);
        //save model
        $this->model->save();
        foreach($this->storeViews as $storeId) {
          //$this->model->setStoreId($storeId)->save();
        }

        $this->results['success'] = TRUE;
      } catch(Exception $e) {
        $this->results['success'] = FALSE;
        $this->errors[] = array(
         'error' => $e->getMessage(),
         'location' => __METHOD__ . ' - L#' . __LINE__
        );
      }
      //do stuff if book model is saved succesfully and is active
      if(empty($this->errors) && ($this->isActive)) {
        //do stuff that has to be done after the product has been saved once
        $this->model->setStockData($this->stockArray);
        $this->model->save();
        Mage::getSingleton('cataloginventory/stock_status')->updateStatus($this->model->getId());

        //set links now that product is saved.
        if(in_array('setTitleLink',$this->links)) $this->setTitleLink(); //save title links

        //set the category positions if not a title type product
        if($this->typeData['type'] != 'title_product') {
          $this->setCategoryPositions();
        }

      }
    } else {
      //not active, and new. so don't bother pushing or saving..
      $this->results['success'] = TRUE;
    }

    //return
    $this->results['sku'] = $this->sku;
    $this->results['errors'] = $this->errors;
    return $this->results;
  }

  //function to setup the category/category  id array
  protected function getCategories($vals) {
    $catIdArray = array(); //will hold the cat ids we're adding to this product
    //lets get the ids for the categories
    foreach($vals as $category => $position) {
      $category = trim($category);
      if(($category == '') || ($category == ' ') || ($category == false)) continue;
      //check if the category already exists
      $categoryCollection = Mage::getResourceModel('catalog/category_collection')
       ->addFieldToFilter('name', $category);
      if($categoryCollection->getSize() > 0) {
        foreach($categoryCollection as $cat) {
          //add its id to our list
          $catIdArray[$cat->getEntityId()] = $position;
        }
      }
      else {
        //category doesnt exist, lets create it
        try {
          $categoryModel = Mage::getModel('catalog/category');
          $categoryModel->setName($category);
          //$category->setUrlKey('your-cat-url-key');
          $categoryModel->setIsActive(1);
          $categoryModel->setDisplayMode('PRODUCTS'); //its a new cat, so we wont have a cms block to assoc. we can override this later in admin panel..
          //$category->setIsAnchor(0); //for active anchor
          //$categoryModel->setStoreId(Mage::app()->getStore()->getId());
          $parentCategory = Mage::getModel('catalog/category')->load($this->genreRootCat);
          $categoryModel->setPath($parentCategory->getPath());
          $categoryModel->setIncludeInMenu(0);
          $categoryModel->save();
          //get its id, and add it to our list
          $catIdArray[] = $categoryModel->getEntityId();
        } catch(Exception $e) {
          $this->results['success'] = FALSE;
          $this->errors[] = array(
           'error' => 'error creating a category for category: "' . $category . ' msg:' . $e->getMessage(),
           'location' => __METHOD__ . ' - L#' . __LINE__
          );
        }
      }
    }
    //return the category id array
    return $catIdArray;
  }

  protected function getPIDInfo() {
    switch($this->pid) {
      case (1):
        $this->labelSuffix = ' - Library CD';
        $this->productType = 'simple';
        $this->productGroup = 'Library CD';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (5):
        $this->labelSuffix = ' - MP3 CD';
        $this->productType = 'simple';
        $this->productGroup = 'MP3 CD';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (6):
        $this->labelSuffix = ' - Playaway';
        $this->productType = 'simple';
        $this->productGroup = 'Playaway';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (8):
        $this->labelSuffix = ' - Download';
        $this->productType = 'virtual';
        $this->productGroup = 'Download';
        $this->stockArray['max_sale_qty'] = 1;
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (337):
        $this->labelSuffix = ' - Download';
        $this->productType = 'virtual';
        $this->productGroup = 'Download';
        $this->stockArray['max_sale_qty'] = 1;
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (10):
        $this->labelSuffix = ' - Rental';
        $this->productType = 'virtual';
        $this->productGroup = 'Rental';
        $this->stockArray['max_sale_qty'] = 1;
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (41):
        $this->labelSuffix = ' - Paperback';
        $this->productType = 'simple';
        $this->productGroup = 'Paperback';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (54):
        $this->labelSuffix = ' - Hardcover';
        $this->productType = 'simple';
        $this->productGroup = 'Hardcover';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (60):
        $this->labelSuffix = ' - Daisy Master';
        $this->productType = 'virtual';
        $this->productGroup = 'Daisy Master';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (69):
        $this->labelSuffix = ' - Mass Market Paperback';
        $this->productType = 'simple';
        $this->productGroup = 'Mass Market Paperback';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (3):
        $this->labelSuffix = ' - Retail CD';
        $this->productType = 'simple';
        $this->productGroup = 'Retail CD';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (72):
        $this->labelSuffix = ' - Retail CD';
        $this->productType = 'simple';
        $this->productGroup = 'Retail CD';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (75):
        $this->labelSuffix = ' - Retail CD';
        $this->productType = 'simple';
        $this->productGroup = 'Retail CD';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (84):
        $this->labelSuffix = ' - Retail CD';
        $this->productType = 'simple';
        $this->productGroup = 'Retail CD';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (99):
        $this->labelSuffix = ' - Retail CD';
        $this->productType = 'simple';
        $this->productGroup = 'Retail CD';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (106):
        $this->labelSuffix = ' - Retail CD';
        $this->productType = 'simple';
        $this->productGroup = 'Retail CD';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (301):
        $this->labelSuffix = ' - Retail CD';
        $this->productType = 'simple';
        $this->productGroup = 'Retail CD';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (310):
        $this->labelSuffix = ' - Retail CD';
        $this->productType = 'simple';
        $this->productGroup = 'Retail CD';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (313):
        $this->labelSuffix = ' - Retail CD';
        $this->productType = 'simple';
        $this->productGroup = 'Retail CD';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (326):
        $this->labelSuffix = ' - Premium Mass Market Paperback';
        $this->productType = 'simple';
        $this->productGroup = 'Premium Mass Market Paperback';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (338):
        $this->labelSuffix = ' - Retail CD';
        $this->productType = 'simple';
        $this->productGroup = 'Retail CD';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (344):
        $this->labelSuffix = ' - Retail CD';
        $this->productType = 'simple';
        $this->productGroup = 'Retail CD';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (350):
        $this->labelSuffix = ' - Retail CD';
        $this->productType = 'simple';
        $this->productGroup = 'Retail CD';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
      case (356):
        $this->labelSuffix = ' - Retail CD';
        $this->productType = 'simple';
        $this->productGroup = 'Retail CD';
        $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
        break;
        /*case (EPUB TEMP!!!):
          $this->labelSuffix = ' - ePub';
          $this->productType = 'simple';
          $this->productGroup = 'ePub';
          $this->associationPosition = Mage::Helper('blackstone_products')->getFormatAssociationPosition($this->pid);
          break;*/
      default:
        $this->results['success'] = FALSE;
        $this->errors[] = array(
         'error' => "PID '".$this->pid."' does not match any known entity. Is this a new format?",
         'location' => __METHOD__ . ' - L#' . __LINE__
        );
        break;
    }
  }

  protected function getProviderCodes(){
    if(!$this->productProviderCodes) {
      $codes = array();
      $attr = Mage::getModel('catalog/resource_eav_attribute');
      $attr->loadByCode("catalog_product", "provider");
      $opts = $attr->getSource()->getAllOptions(TRUE, TRUE);

      foreach($opts as $opt) {
        if(isset($opt["catalog_id"]) && ($opt["catalog_id"] != false) && isset($opt["value"]) && ($opt["value"] != false)) {
          $codes[$opt['catalog_id']] = $opt['value'];
        }
      }
      $this->productProviderCodes = $codes;
    }
    return $this->productProviderCodes;
  }

  protected function getImprintCodes(){
    if(!$this->productImprintCodes) {
      $codes = array();
      $attr = Mage::getModel('catalog/resource_eav_attribute');
      $attr->loadByCode("catalog_product", "imprint");
      $opts = $attr->getSource()->getAllOptions(TRUE, TRUE);

      foreach($opts as $opt) {
        if(isset($opt["catalog_id"]) && ($opt["catalog_id"] != false) && isset($opt["value"]) && ($opt["value"] != false)) {
          $codes[$opt['catalog_id']] = $opt['value'];
        }
      }
      $this->productImprintCodes = $codes;
    }
    return $this->productImprintCodes;
  }

  protected function getDealCodes(){
    if(!$this->productDealCodes) {
      $codes = array();
      $attr = Mage::getModel('catalog/resource_eav_attribute');
      $attr->loadByCode("catalog_product", "deal");
      $opts = $attr->getSource()->getAllOptions(TRUE, TRUE);

      foreach($opts as $opt) {
        if(isset($opt["catalog_id"]) && ($opt["catalog_id"] != false) && isset($opt["value"]) && ($opt["value"] != false)) {
          $codes[$opt['catalog_id']] = $opt['value'];
        }
      }
      $this->productDealCodes = $codes;
    }
    return $this->productDealCodes;
  }

  protected function getFormatCodes(){
    if(!$this->productGroupCodes) {
      $codes = array();
      $attr = Mage::getModel('catalog/resource_eav_attribute');
      $attr->loadByCode("catalog_product", "product_group");
      $opts = $attr->getSource()->getAllOptions(TRUE, TRUE);

      foreach($opts as $opt) {
        if(isset($opt["label"]) && ($opt["label"] != false) && isset($opt["value"]) && ($opt["value"] != false)) {
          $codes[$opt['label']] = $opt['value'];
        }
      }
      $this->productGroupCodes = $codes;
    }
    return $this->productGroupCodes;
  }

  //set title specific data
  protected function setTitleData() {

  }

  //link a product to a title
  protected function setTitleLink() {
    //check that we have a book_id to link to
    if(isset($this->typeData['link_id'])) {
      $bookId = $this->typeData['link_id'];
    } else {
      $this->results['success'] = FALSE;
      $this->errors[] = array(
       'error' => "type_data['link_id'] param is missing",
       'location' => __METHOD__ . ' - L#' . __LINE__
      );
      return;
    }

    //get the position, or set to default
    /*if(isset($this->typeData['link_position'])) {
      $position = $this->typeData['link_position'];
    } else {
      $position = $this->associationPosition;
    }*/
    $position = $this->associationPosition;
    $info = array(
     'position' => $position,
     'qty' => 0
    );

    $titleProdId = Mage::getModel('catalog/product')->getResource()->getIdBySku($bookId);
    if($titleProdId) {
      //we have a grouped prod id
      try {
        $products_links = Mage::getModel('catalogimporter/product_link_api');
        $products_links->assignById('grouped',$titleProdId,$this->model->getId(),$info);
      } catch (Exception $e) {
        $this->results['success'] = FALSE;
        $this->errors[] = array(
         'error' => 'cannot save relation to title '.$bookId.' : '.$e->getMessage(),
         'location' => __METHOD__ . ' - L#' . __LINE__
        );
      }
      //no grouped prod found, so lets disable this one
      $this->isActive = false;
      $this->model->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
    } else {
      //no parent product found..
      $this->errors[] = array(
       'error' => 'cannot save relation to title '.$bookId.'. Title not found',
       'location' => __METHOD__ . ' - L#' . __LINE__
      );
      $this->results['success'] = FALSE;
    }

    //$relation = Mage::getResourceModel('blackstone_titles/title_product')->load($this->model->getId(),'product_id');
    //$relation->saveProductRelation($this->model, $info);
  }

  //function to get the website id's
  protected function getArrayOfWebsites(){
    if(empty($this->websiteIds) || (empty($this->storeViews))) {
      $sites = Mage::app()->getWebsites();
      $site_list = array();
      $store_list = array();
      foreach($sites as $site) {
        $site_list[] = $site->getWebsiteId();
        foreach ($site->getStores() as $store) {
          //if($store->getIsActive()) {
          $store_list[] = $store->getStoreId();
          //}
        }
      }
      $this->websiteIds = $site_list;
      $this->storeViews = $store_list;
    }
  }

  //function to put the product into the appropriate genre categories while preserving custom category associations
  protected function placeInCats() {
    $existingCatIds = array();
    if(!$this->isNew) {
      $this->getCustomCategoryIds();
      //lets preserve the custom category associations
      $existingCatIds = $this->model->getCategoryIds();
      if(isset($existingCatIds) && !empty($existingCatIds)) {
        $existingCatIds = array_intersect($existingCatIds, $this->customCats);
      }
    }
    $existingCatIdsKeyed = array();
    foreach($existingCatIds as $catId) {
      $existingCatIdsKeyed[$catId] = 999;
    }

    $catIdsArray = $this->getCategories($this->jsonData['categories']);
    $this->genreCats = $catIdsArray;
    $catIds = $existingCatIdsKeyed + $catIdsArray; //add rather than merge to preserve keys
    $catIds = array_keys($catIds);
    $this->allCats = $catIds;
    $this->model->setCategoryIds($catIds);
  }

  //function to adjust the position of the product in the category
  protected function setCategoryPositions(){
    $adapter = Mage::getSingleton('core/resource')->getConnection('core_write');
    if(is_array($this->genreCats)){
      foreach($this->genreCats as $categoryId => $position){
        //First make sure we've got all the right keys in all the right places
        if(isset($categoryId) && (isset($position)) && ($this->model->getId())) {
          //Borrowed from Mage_Catalog_Model_Resource_Eav_Mysql4_Category->_saveCategoryProducts
          $where = $adapter->quoteInto('category_id=?  AND ', (int)$categoryId)
           . $adapter->quoteInto('product_id=?', (int)$this->model->getId());
          $bind  = array('position' => (int)$position);
          $adapter->update(Mage::getSingleton("core/resource")->getTableName('catalog/category_product'), $bind, $where);

        }//Otherwise do nothing
      }
    }
  }

  //function to get all the ids of the sub cats in custom categories root
  protected function getCustomCategoryIds() {
    if(empty($this->customCats)) {
      //Load up our category model for the "Custom" category
      $customCat = Mage::getModel("catalog/category")->load($this->customRootCat);
      //(Recursively) get all children for the merch category, and stick them in an array
      $this->customCats = $customCat->getChildren(TRUE);
      $this->customCats = explode(',',$this->customCats);
      //Add the merchandising root category ID to our array as well
      $this->customCats[] = $this->customRootCat;
    }
  }

  protected function reset() {
    $this->scope = false;
    $this->results = array();
    $this->isActive = false;
    $this->errors = array();
    $this->sku = false;
    $this->model = false;
    $this->isNew = false;
    $this->modelData = false;
    $this->jsonData = false;
    $this->productType = false;
    $this->labelSuffix = false;
    $this->productGroup = false;
    $this->links = array();
    $this->genreCats = array();
    $this->allCats = array();
    $this->associationPosition = 999;
    $this->stockArray = array(
     'use_config_manage_stock' => 0, //'Use config settings' checkbox
     'manage_stock' => 0, //manage stock
     'min_sale_qty' => 1, //Minimum Qty Allowed in Shopping Cart
     'max_sale_qty' => 9999, //Maximum Qty Allowed in Shopping Cart
     'is_in_stock' => 1, //Stock Availability
     'qty' => 9999 //qty
    );
  }
}