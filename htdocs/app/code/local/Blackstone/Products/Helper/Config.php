<?php
/**
 * Created by PhpStorm.
 * User: erik.petterson
 * Date: 3/13/2017
 * Time: 3:38 PM
 */

class Blackstone_Products_Helper_Config extends Mage_Core_Helper_Abstract {
  //function to get the pid's of downloadable products
  public function getDownloadableCatalogPids() {
    $downloadablePids = array(
     8,
     337
    );
    return $downloadablePids;
  }

  //function to get the pids of rental products
  public function getRentalCatalogPids() {
    $rentalPids = array(
     10
    );
    return $rentalPids;
  }

  //function to get a 'pre approved' list of territories. Can be sold if in this array, no need to check
  public function getPreApprovedTerritories() {
    return array(
     'us'
    );
  }
}