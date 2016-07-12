<?php 

require('../config.php');
dol_include_once('/supplierprice/config.php');
dol_include_once('custom/supplierprice/lib/supplierprice.lib.php');
dol_include_once('custom/supplierprice/class/supplierprice.class.php');
dol_include_once('product/class/product.class.php');
$id = GETPOST('idprod_supplierprice');

$TproductSupplierPrices = select_all_supplierprices($id);
$PDOdb = new TPDOdb;
$TData = array();


foreach ($TproductSupplierPrices as $idsupplierprice) {
	$supplierprice = new TSupplierPrice;
	$supplierprice->load($PDOdb, $idsupplierprice);
	$TData[] = array(
			'id' => $supplierprice->rowid,
			'ref_fourn' => $supplierprice->ref_fourn
	);
}

var_dump($TData);
__out($TData);
