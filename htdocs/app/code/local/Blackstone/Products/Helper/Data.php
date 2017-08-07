<?php
/**
 * Created by PhpStorm.
 * User: erik.petterson
 * Date: 11/11/2015
 * Time: 9:39 AM
 */ 
class Blackstone_Products_Helper_Data extends Mage_Core_Helper_Abstract {

  protected $cdnBaseUrl = false;

  //function that gets the product group icon class from the product group ID
  public function getGroupIconClass($productGroupId) {
    //todo add a config in the backend to set these icons
    switch($productGroupId) {
      case ('84'): //download
        return 'icon-download';
        break;
      case ('85'): //rental
        return 'icon-rental-format';
        break;
      case ('86'): //Retail CD
        return 'icon-cd-format';
        break;
      case ('87'): //Library CD
        return 'icon-libraryCD-format';
        break;
      case ('88'): //MP3 CD
        return 'icon-mp3cd-format';
        break;
      case ('89'): //Playaway
        return 'icon-Playaway-format';
        break;
      case ('90'): //Paperback
        return 'icon-paperback-format';
        break;
      case ('91'): //Hardcover
        return 'icon-hardcover-format';
        break;
      case ('92'): //mass market paperback
        return 'icon-mass-market-format';
        break;
      case ('93'): //premium mass market paperback
        return 'icon-premium-mass-market-format';
        break;
      default:
        return 'icon-question2';
        break;
    }
  }

  //function to get the url of the sample mp3 file
  public function getSampleUrl($sku) {
    $path = substr($sku, 0, 1) . '/' . substr($sku, 1, 1) . '/' . $sku . '/' . $sku . '-sample.mp3';
    $url = $this->getCdnBaseUrl() . $path;
    return $url;
  }

  //function to return the base cdn url, calls from var or loads if needed
  public function getCdnBaseUrl() {
    if(!$this->cdnBaseUrl) {
      $this->cdnBaseUrl = trim(Mage::getStoreConfig('cdn/cdn/cdn_base_url'));
    }
    return $this->cdnBaseUrl;
  }

  //function to get the associated position of product formats
  public function getFormatAssociationPosition($PID) {
    $position = false;
    switch($PID) {
      case (1):
        $position = 250;
        break;
      case (5):
        $position = 350;
        break;
      case (6):
        $position = 600;
        break;
      case (8):
        $position = 50;
        break;
      case (337):
        $position = 50;
        break;
      case (10):
        $position = 100;
        break;
      case (41):
        $position = 500;
        break;
      case (54):
        $position = 400;
        break;
      case (60):
        $position = 650;
        break;
      case (69):
        $position = 550;
        break;
      case (3):
        $position = 200;
        break;
      case (72):
        $position = 200;
        break;
      case (75):
        $position = 200;
        break;
      case (84):
        $position = 200;
        break;
      case (99):
        $position = 200;
        break;
      case (106):
        $position = 200;
        break;
      case (301):
        $position = 200;
        break;
      case (313):
        $position = 200;
        break;
      case (326):
        $position = 450;
        break;
      case (338):
        $position = 200;
        break;
      case (344):
        $position = 200;
        break;
      case (350):
        $position = 200;
        break;
      case (356):
        $position = 200;
        break;
      /*case (EPUB TEMP!!!):
        $position = 450;
        break;*/
      default:
        $position = 999;
        break;
    }
    return $position;
  }


  //function to check if a product can be sold based on the territory
  public function checkIfTerritorySalable($product) {
    if(!Mage::getStoreConfig('blackstone_geoip/geoip_general/geoip_restrict')) {
      //geo ip restriction is off. return salable.
      return true;
    }

    $userCode = strtolower($_SERVER['HTTP_X_GEOIP']);
    if(in_array($userCode,Mage::helper('blackstone_products/config')->getPreApprovedTerritories())) {
      //pre approved, don't bother checking prod data
      return true;
    }

    if(in_array($userCode, explode(',',$product->getTerritories()))) {
      return true;
    } else {
      return false;
    }
  }


  //function to check if a product can be sold based on preorder and release date
  public function checkIfSalable($releaseDate, $catalogPid) {
    $preorderFlag = $this->isPreorder($catalogPid);
    $time = Mage::getModel('core/date')->date('Y-m-d H:i:s');
    $uTime = strtotime($time);
    $uReleaseDate = strtotime($releaseDate);
    if(($uReleaseDate <  $uTime) ||($preorderFlag)) {
      //released in the past, or preorder is true
      return true;
    }
    return false;
  }

  //function to check if a product is past release date
  public function checkIfReleased($releaseDate) {
    $time = Mage::getModel('core/date')->date('Y-m-d H:i:s');
    $uTime = strtotime($time);
    $uReleaseDate = strtotime($releaseDate);
    if(($uReleaseDate <  $uTime)) {
      //released in the past
      return true;
    }
    return false;
  }

  //function to check if preorder based on catalog pid
  public function isPreorder($catalogPid) {
    if(in_array($catalogPid,Mage::helper('blackstone_products/config')->getRentalCatalogPids())) {
      //no preorders for rentals
      return false;
    } else {
      //pre order on everything except rentals..
      return true;
    }
  }

  //function to check if is in preorder mode
  public function isInPreorder($releaseDate, $catalogPid) {
    $preorderFlag = $this->isPreorder($catalogPid);
    $time = Mage::getModel('core/date')->date('Y-m-d H:i:s');
    $uTime = strtotime($time);
    $uReleaseDate = strtotime($releaseDate);
    if(($uReleaseDate >  $uTime) && ($preorderFlag)) {
      //released in the future, and preorder is true
      return true;
    }
    return false;
  }
}