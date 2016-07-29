<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file		lib/supplierprice.lib.php
 *	\ingroup	supplierprice
 *	\brief		This file is an example module library
 *				Put some comments here
 */

function supplierpriceAdminPrepareHead()
{
    global $langs, $conf;

    $langs->load("supplierprice@supplierprice");

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/supplierprice/admin/supplierprice_setup.php", 1);
    $head[$h][1] = $langs->trans("Parameters");
    $head[$h][2] = 'settings';
    $h++;
    $head[$h][0] = dol_buildpath("/supplierprice/admin/supplierprice_about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    //$this->tabs = array(
    //	'entity:+tabname:Title:@supplierprice:/supplierprice/mypage.php?id=__ID__'
    //); // to add new tab
    //$this->tabs = array(
    //	'entity:-tabname:Title:@supplierprice:/supplierprice/mypage.php?id=__ID__'
    //); // to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'supplierprice');

    return $head;
}

function getSql($productId)
{
	global $conf;
	
	// TODO Manque le total
	//refaire un test si muiltidevise activé et reprendre la requete pour adapter si multidevise activé
	$sql = 'SELECT sp_c.rowid AS id, sp_c.type_price, count.label AS Pays, soc.nom AS Societe, sp_c.tva_tx, sp_c.qty, sp_c.remise_percent, sp_c.tva_tx, sp_c.price, sp_c.date_start, sp_c.date_end, " " AS Actions ';
	//TODO ici viendra potentiellement le complément multidevise
	if($conf->multidevise->enabled){}
	$sql .= 'FROM '.MAIN_DB_PREFIX.'supplierprice_conditionnement sp_c ';
	$sql .= 'left JOIN '.MAIN_DB_PREFIX.'societe soc ON soc.rowid=sp_c.fk_soc ';
	$sql .= 'left JOIN '.MAIN_DB_PREFIX.'c_country count ON count.rowid=sp_c.fk_country ';
	$sql .= 'left JOIN '.MAIN_DB_PREFIX.'categorie categ ON categ.rowid=sp_c.fk_categorie_fournisseur ';
	$sql .= 'WHERE sp_c.fk_product='.$productId.' ';
	
	
	
	
	
	return $sql;
}

function get_all_products(){
	global $db;
	
	$TData = array();
	
	$sql = 'SELECT rowid ';
	$sql .= 'FROM '.MAIN_DB_PREFIX.'product ';
	$resql = $db->query($sql);
	
	 if ($resql){
			while ($line = $db->fetch_object($resql)){
				
				$TData[] = $line->rowid;
			}
		}
	 return $TData;
	
}


function get_all_prices_fourn($id=''){
	global $db;
	
	$TData = array();
	
	$sql = 'SELECT pfp.rowid ';
	$sql .= 'FROM '.MAIN_DB_PREFIX.'product_fournisseur_price pfp ';
	if($id!='') $sql .= 'WHERE fk_product = '.$id.' ';
	$resql = $db->query($sql);
	
	if($resql){
		while($line = $db->fetch_object($resql)){
			$TData[]=$line->rowid;
		}
	}
	return $TData;
	
}

function get_all_supplier_prices($fk_prixfourn, $date_deb=0, $datefin=0){
	global $db;
	
	$TData = array();
	
	$sql = 'SELECT rowid ';
	$sql .= 'FROM '.MAIN_DB_PREFIX.'supplierprice_conditionnement ';
	$sql .= 'WHERE (fk_product_fourn_price ='.$fk_prixfourn.') ';
	if (!empty($date_deb)) $sql .= 'AND ('.$date_deb.' BETWEEN (date_start AND date_end)) ';
	$sql .= 'ORDER BY date_start DESC, remise_percent DESC ';
	
	$resql = $db->query($sql);
	
	if ($resql){
		while ($line = $db->fetch_object($resql)){
			$TData[] = $line->rowid;
		}
	}
	
	
	return $TData;
}

