<?php

class Csaw_BarcodeScanner_Model_Find extends Mage_Core_Model_Abstract {

  public function __construct()
  {
    $this->_init('barcodescanner/find');
  }

  public function findProduct($code, $identifiers)
  {

    $item_found = null;
    //if code contains only digits then it must be an id, GTIN or EAN
    if(ctype_digit($code))
    {
      $length = sizeof($identifiers);

      if($length == 1)
      {
        $product = $this->getProduct($code, $identifiers);
      } else
      {
        $product = $this->searchByMultipleIdentifiers($code, $identifiers);
      }

    } else
    {
      if (in_array("SKU", $identifiers))
      {
        //can only be a SKU based on our individual products
        $product = Mage::getModel('catalog/product')->loadByAttribute('SKU', $code);
      }
    }

    if(isset($product))
    {
      $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
      $item_found = Mage::getModel('barcodescanner/item', array('product' => $product->getData(),
                                                          'stock' =>  $stock->getQty()));
    }

    Mage::log($product, null, 'item.log');
    return $item_found;

  }

  /**
  * Get product from database based on the search code and identifiers
  */
  public function getProduct($code, $attribute)
  {
    $product = null;
    //if it's a GTIN (most likely)
    if(in_array("GTIN", $attribute))
    {
        Mage::log('in gtin', null, 'mylogfile.log');
        $product = Mage::getModel('catalog/product')->loadByAttribute('c2c_gtin', $code);
    } else if (in_array("EAN", $attribute))
    {
        $product = Mage::getModel('catalog/product')->loadByAttribute('EAN', $code);
        Mage::log('in ean', null, 'mylogfile.log');

    } else if (in_array("ID", $attribute))
    {
        $product = Mage::getModel('catalog/product')->load($code);
        Mage::log('ID', null, 'mylogfile.log');

    }  else if (in_array("SKU", $attribute))
    {
        $product = Mage::getModel('catalog/product')->loadByAttribute('SKU', $code);
        Mage::log('SKU', null, 'mylogfile.log');
    }

    return $product;
  }

  /**
  * Need to get all products by multiple identifiers and return those products in an array
  */
  public function searchByMultipleIdentifiers($code, $identifiers)
  {
    $products_found = array();
    foreach($identifiers as $attribute)
    {
      $product = getProduct($code, $identifiers);

      if(isset($product) && $product != null)
      {
        array_push($products_found, $product);
      }
    }
    return $products_found;
  }



}
