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
 * 	\file		core/triggers/interface_99_modMyodule_supplierpricetrigger.class.php
 * 	\ingroup	supplierprice
 * 	\brief		Sample trigger
 * 	\remarks	You can create other triggers by copying this one
 * 				- File name should be either:
 * 					interface_99_modMymodule_Mytrigger.class.php
 * 					interface_99_all_Mytrigger.class.php
 * 				- The file must stay in core/triggers
 * 				- The class name must be InterfaceMytrigger
 * 				- The constructor method must be named InterfaceMytrigger
 * 				- The name property name must be Mytrigger
 */

/**
 * Trigger class
 */
class Interfacesupplierpricetrigger
{

    private $db;

    /**
     * Constructor
     *
     * 	@param		DoliDB		$db		Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;

        $this->name = preg_replace('/^Interface/i', '', get_class($this));
        $this->family = "demo";
        $this->description = "Triggers of this module are empty functions."
            . "They have no effect."
            . "They are provided for tutorial purpose only.";
        // 'development', 'experimental', 'dolibarr' or version
        $this->version = 'development';
        $this->picto = 'supplierprice@supplierprice';
    }

    /**
     * Trigger name
     *
     * 	@return		string	Name of trigger file
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Trigger description
     *
     * 	@return		string	Description of trigger file
     */
    public function getDesc()
    {
        return $this->description;
    }

    /**
     * Trigger version
     *
     * 	@return		string	Version of trigger file
     */
    public function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version == 'development') {
            return $langs->trans("Development");
        } elseif ($this->version == 'experimental')

                return $langs->trans("Experimental");
        elseif ($this->version == 'dolibarr') return DOL_VERSION;
        elseif ($this->version) return $this->version;
        else {
            return $langs->trans("Unknown");
        }
    }

    /**
     * Function called when a Dolibarrr business event is done.
     * All functions "run_trigger" are triggered if file
     * is inside directory core/triggers
     *
     * 	@param		string		$action		Event action code
     * 	@param		Object		$object		Object
     * 	@param		User		$user		Object user
     * 	@param		Translate	$langs		Object langs
     * 	@param		conf		$conf		Object conf
     * 	@return		int						<0 if KO, 0 if no triggered ran, >0 if OK
     */
    public function run_trigger($action, $object, $user, $langs, $conf){
    	global $db;
		
		if (empty($conf->fournisseur->enabled)) return 0;
		
		
		dol_include_once('/supplierprice/class/supplierprice.class.php');
		dol_include_once('/supplierprice/lib/supplierprice.lib.php');
        // Put here code you want to execute when a Dolibarr business events occurs.
        // Data and type of action are stored into $object and $action
        // Uers
        
        if ($action == 'LINEORDER_INSERT' || $action == 'LINEBILL_SUPPLIER_CREATE') {
            
			if ($action == 'LINEORDER_INSERT' && get_class($object)!='CommandeFournisseurLigne'){
				return 0;
			}
			$PDOdb = new TPDOdb;
			$date_start_line = strtotime(str_replace('/', '-', GETPOST('date_start')));
			$date_end_line = strtotime(str_replace('/', '-',GETPOST('date_end')));
			$idprodfournprice = GETPOST('idprodfournprice');
			$idProduit = GETPOST('id');
			
			$pricefourn = new ProductFournisseur($db);
			$supplierprice = new TSupplierPrice;
			
			$pricefourn->fetch_product_fournisseur_price($idprodfournprice);
			
			$idSupplierprice = get_all_supplier_prices($idprodfournprice);
			$res = $supplierprice->load($PDOdb, $idSupplierprice);
			if($res){
				
				if(!empty($date_start_line)){
					if ($date_end_line > $supplierprice->date_end){
						setEventMessage('Les dates saisies ne correspondent pas à celles du tarif choisi', 'errors');
						return -1;
					}else if($date_start_line < $supplierprice->date_start){
						setEventMessage('Les dates saisies ne correspondent pas à celles du tarif choisi', 'errors');
						return -1;
					}else{
						setEventMessage('Ligne ajoutée', 'msgs');
					}
					
				}else{
					var_dump(strtotime(date('Y-m-d')), $supplierprice->date_end );
					if ( strtotime(date('Y-m-d')) < $supplierprice->date_start ){
						setEventMessage('La date du jour ne correspond pas aux dates du tarif choisi', 'errors');
						return -1;
					}else if($supplierprice->date_end < strtotime(date('Y-m-d'))){
						setEventMessage('La date du jour ne correspond pas aux dates du tarif choisi', 'errors');
						return -1;
					}else{
						setEventMessage('Ligne ajoutée', 'msgs');
					}
					
				}
			}
			
			
        } 

        return 0;
    }
}