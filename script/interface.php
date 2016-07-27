<?php 

require('../config.php');
dol_include_once('/supplierprice/config.php');
dol_include_once('custom/supplierprice/lib/supplierprice.lib.php');
dol_include_once('custom/supplierprice/class/supplierprice.class.php');
dol_include_once('fourn/class/fournisseur.commande.class.php');
dol_include_once('fourn/class/fournisseur.facture.class.php');
dol_include_once('product/class/product.class.php');

$action = GETPOST('action');

$PDOdb = new TPDOdb;


switch ($action) {
	case 'select_produit':
		$id = GETPOST('idprod_supplierprice');
		$TproductSupplierPrices = select_all_supplierprices($id);
		
		$TData = array();
		
		foreach ($TproductSupplierPrices as $idsupplierprice) {
			$supplierprice = new TSupplierPrice;
			$supplierprice->load($PDOdb, $idsupplierprice);
			$TData[] = array(
					'id'        => $supplierprice->rowid,
					'ref_fourn' => $supplierprice->ref_fourn,
					'qty'       => $supplierprice->qty,
					'total'     => !empty($supplierprice->remise_percent) ? (($supplierprice->price-($supplierprice->price*($supplierprice->remise_percent/100))) * $supplierprice->qty) : ($supplierprice->price * $supplierprice->qty)
			);
		}
		__out($TData);
		
		break;
		
	case 'appliquer_tarif':
		$id = GETPOST('idtarif_supplierprice');
		
		$TData = array();
		
		$supplierprice = new TSupplierPrice;
		$supplierprice->load($PDOdb, $id);
		$TData = array(
					'id'        => $supplierprice->rowid,
					'ref_fourn' => $supplierprice->ref_fourn,
					'qty'       => $supplierprice->qty,
					'total'     => !empty($supplierprice->remise_percent) ? (($supplierprice->price-($supplierprice->price*($supplierprice->remise_percent/100))) * $supplierprice->qty) : ($supplierprice->price * $supplierprice->qty),
					'TVA'       => $supplierprice->tva_tx,
					'pu'        => empty($supplierprice->price) ? ($supplierprice->remise_percent) : ($supplierprice->price-($supplierprice->price*($supplierprice->remise_percent/100))),
					'reduc'     => $supplierprice->remise_percent
			);
		__out($TData);
		break;
		
	case 'addLine':
		$element = GETPOST('element');
		$idElement=GETPOST('idElement');
		$idProd = GETPOST('idprod');
		$fk_supplier = GETPOST('fk_supplier');
		$qty = GETPOST('qty');
		$pu=GETPOST('pu');
		$tva = GETPOST('TVA');
		$reduc = GETPOST('reduc_supplierprice');
		$totalHT = GETPOST('totalHT');
		$idTarif = GETPOST('idSupplierPrice');
		
		$supplierprice = new TSupplierPrice;
		$supplierprice->load($PDOdb, $idTarif);
		
		
		$object = new $element($db);
		$object->fetch($idElement);
		
		if($element=='CommandeFournisseur'){
			$res = $object->addline('', $pu, $qty, $tva, 0.0, 0.0, $idProd, $idTarif, $supplierprice->ref_fourn, $reduc);
		}else if($element=='FactureFournisseur'){
			$res = $object->addline('', $pu, $tva, $object->localtax1, $object->localtax2, $qty, $idProd, $reduc);
		}
		
		__out($object);
		
		break;
	default:
		
		break;
}	
	
