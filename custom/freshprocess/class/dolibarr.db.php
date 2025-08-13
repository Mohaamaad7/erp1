<?php
require_once(dirname(__FILE__).'/multipdo.php');

/*
*	Dolibarr Base
*/

class DolibarrBase {

    private $_CONNECT;
    private $prefix;

    public function __construct ($params, $prefix='llx_') {
        $this->prefix = $prefix;
        $this->_CONNECT = MultiPDO::init($params);
    }

    public function setPrefix ($prefix='llx_') {
        $this->prefix = $prefix;
    }

    public function getAllClients () {
        return $this->_CONNECT->request('SELECT * FROM '.$this->prefix.'societe', true);
    }

    public function getDevis () {
        return $this->_CONNECT->request('SELECT A.* FROM '.$this->prefix.'propal A WHERE A.fk_statut = 2', true);
    }

    public function getCmd () {
        return $this->_CONNECT->request('SELECT A.* FROM '.$this->prefix.'commande A WHERE A.fk_statut = 1', true);
    }

    public function getAllDevis () {
        return $this->_CONNECT->request('SELECT * FROM '.$this->prefix.'propal', true);
    }

    public function getDevisById ($devis_id) {
        $devis_id = (int)$devis_id;
        return $this->_CONNECT->request('SELECT * FROM '.$this->prefix.'propal WHERE rowid = '.$devis_id, true);
    }

    public function getDevisDetails ($devis_id) {
        $devis_id = (int)$devis_id;
        return $this->_CONNECT->request('SELECT * FROM '.$this->prefix.'propaldet WHERE fk_propal = '.$devis_id, true);
    }

    public function getDevisBySocieteId ($idSoc) {
        return $this->_CONNECT->request('SELECT A.* FROM '.$this->prefix.'propal A WHERE A.fk_statut = 2 AND A.fk_soc = '.(int)$idSoc, true);
    }

    public function getTvaMaxDevis ($idPropal) {
        return $this->_CONNECT->request('SELECT MAX(tva_tx) as total FROM '.$this->prefix.'propaldet WHERE fk_propal = '.(int)$idPropal.' LIMIT 1', true);
    }

    public function getProduct ($product_id) {
        $product_id = (int)$product_id;
        return $this->_CONNECT->request('SELECT * FROM '.$this->prefix.'product WHERE rowid = '.$product_id, true);
    }

    public function getCodeClient ($devis_id) {
        $devis_id = (int)$devis_id;
        $data = $this->_CONNECT->request('SELECT S.code_client FROM '.$this->prefix.'societe S, '.$this->prefix.'propal P WHERE S.rowid = P.fk_soc AND P.rowid = '.$devis_id, true);
        return (isset($data[0]['code_client']) ? $data[0]['code_client'] : "");
    }

    public function getCodeClientCmd ($cmd_id) {
        $cmd_id = (int)$cmd_id;
        $data = $this->_CONNECT->request('SELECT S.code_client FROM '.$this->prefix.'societe S, '.$this->prefix.'commande C WHERE S.rowid = C.fk_soc AND C.rowid = '.$cmd_id, true);
        return (isset($data[0]['code_client']) ? $data[0]['code_client'] : "");
    }

    public function getSocieteIdByFactNumber ($facnumber) {
        return $this->_CONNECT->request('
            SELECT A.*
            FROM '.$this->prefix.'societe A, '.$this->prefix.'facture B
            WHERE A.rowid = B.fk_soc
            AND B.facnumber = "'.str_replace('"', '', $facnumber).'"
        ', true);
    }

    public function getSocieteIdByPropalRef ($propalref) {
        return $this->_CONNECT->request('
            SELECT A.*
            FROM '.$this->prefix.'societe A, '.$this->prefix.'propal B
            WHERE A.rowid = B.fk_soc
            AND B.ref = "'.str_replace('"', '', $propalref).'"
        ', true);
    }

    public function getSocieteName ($idSoc) {
        $idSoc = (int)$idSoc;
        $data = $this->_CONNECT->request('SELECT nom FROM '.$this->prefix.'societe WHERE rowid = '.$idSoc, true);
        return (count($data) ? $data[0]['nom'] : "");
    }

    public function getNameProject ($idPropal) {
        $idPropal = (int)$idPropal;
        $data = $this->_CONNECT->request('SELECT projet FROM '.$this->prefix.'propal_extrafields WHERE fk_object = '.$idPropal, true);
        return (count($data) ? $data[0]['projet'] : "");
    }

    public function getNameProjectCmd ($cmd_id) {
        $cmd_id = (int)$cmd_id;
        $data = $this->_CONNECT->request('SELECT projet FROM '.$this->prefix.'commande_extrafields WHERE fk_object = '.$cmd_id, true);
        return (count($data) ? $data[0]['projet'] : "");
    }

    public function getUserPass ($login) {
        $login = Tools::filter($login);
        $data = $this->_CONNECT->request('SELECT pass FROM '.$this->prefix.'user WHERE login = \''.$login.'\'', true);
        return (count($data) ? $data[0]['pass'] : false);
    }

    public function getContactsPropal ($devis_id, $contact_type='suivi') {
        $devis_id = (int)$devis_id;
        /* Calculer la sélection 'fk_c_type_contact' */
        $fk_c_type_contact = 41; // suivi de commande
        // if($contact_type == 'commercial') $fk_c_type_contact = 91; // commercial en charge du projet
        // if($contact_type == 'livraison') $fk_c_type_contact = 102; // livraison
        /* Préparer la requête */
        $sql = '
            SELECT firstname, lastname, email, phone_mobile as phone, \'contact\' as type,
                concat(address, \' \', zip, \' \', town) as address_c
            FROM '.$this->prefix.'socpeople
            WHERE rowid IN (
                SELECT fk_socpeople
                FROM '.$this->prefix.'element_contact C
                WHERE C.element_id = '.$devis_id.'
                AND C.statut = 4
                AND C.fk_c_type_contact = '.$fk_c_type_contact.'
            )
            AND email IS NOT NULL
        ';
        if($fk_c_type_contact == 41) {
            /* Choisir l'entreprise en suivi de commande */
            $sql .= '
                UNION SELECT S.nom, \'\', S.email, S.phone, \'societe\' as type,
                    concat(S.address, \' \', S.zip, \' \', S.town) as address_c
                FROM '.$this->prefix.'societe S, '.$this->prefix.'propal P
                WHERE P.fk_soc = S.rowid
                AND P.rowid = '.$devis_id.'
                AND S.email IS NOT NULL
            ';
        }
        /* Exécuter la requête */
        return $this->_CONNECT->request($sql, true);
    }

    public function getContactsCmd ($cmd_id, $contact_type='suivi') {
        $cmd_id = (int)$cmd_id;
        /* Calculer la sélection 'fk_c_type_contact' */
        $fk_c_type_contact = 101; // suivi de commande
        if($contact_type == 'commercial') $fk_c_type_contact = 91; // commercial en charge du projet
        if($contact_type == 'livraison') $fk_c_type_contact = 102; // livraison
        /* Extraction des champs 'llx_socpeople' */
        $sql = '
            SELECT firstname, lastname, email, phone_mobile as phone, \'contact\' as type,
                concat(address, \' \', zip, \' \', town) as address_c
            FROM '.$this->prefix.'socpeople
            WHERE rowid IN (
                SELECT fk_socpeople
                FROM '.$this->prefix.'element_contact C
                WHERE C.element_id = '.$cmd_id.'
                AND C.statut = 4
                AND C.fk_c_type_contact = '.$fk_c_type_contact.'
            )
            AND email IS NOT NULL
        ';
        /* Extraction des champs 'llx_societe' */
        if($fk_c_type_contact == 101 || $fk_c_type_contact == 102) {
            /* Choisir l'entreprise en suivi de commande / livraison */
            $sql .= '
                UNION SELECT S.nom, \'\', S.email, S.phone, \'societe\' as type,
                    concat(S.address, \' \', S.zip, \' \', S.town) as address_c
                FROM '.$this->prefix.'societe S, '.$this->prefix.'commande CMD
                WHERE CMD.fk_soc = S.rowid
                AND CMD.rowid = '.$cmd_id.'
                AND S.email IS NOT NULL
            ';
        }
        /* Extraction des champs 'llx_user' */
        if($fk_c_type_contact == 91) {
            /* Choisir le commercial en charge de la commande */
            $sql .= '
                UNION SELECT U.firstname, U.lastname, U.email,
                    U.user_mobile as phone, \'user\' as type,
                    concat(U.address, \' \', U.zip, \' \', U.town) as address_c
                FROM '.$this->prefix.'user U, '.$this->prefix.'commande CMD
                WHERE CMD.fk_user_author = U.rowid
                AND CMD.rowid = '.$cmd_id.'
                AND U.email IS NOT NULL
            ';
        }
        /* Exécuter la requête */
        return $this->_CONNECT->request($sql, true);
    }

    public function getProducts ($devis_id) {
        $devis_id = (int)$devis_id;
        /* Première requête avec les ID */
        $output = $this->_CONNECT->request('
            SELECT P.ref, P.label as nom, D.description as details, D.qty as qt
            FROM '.$this->prefix.'propaldet D, '.$this->prefix.'product P
            WHERE D.fk_propal = '.$devis_id.'
            AND D.fk_product = P.rowid
        ', true);
        /* Deuxième requête sans les ID */
        $output = array_merge(
            $output,
            array_map(
                function ($entrie) {
                    $entrie['ref'] = '';
                    $entrie['nom'] = '';
                    return $entrie;
                },
                $this->_CONNECT->request('
                    SELECT D.description as details, D.qty as qt
                    FROM '.$this->prefix.'propaldet D
                    WHERE D.fk_propal = '.$devis_id.'
                ', true)
            )
        );
        return $output;
    }

    public function getProductsCmd ($cmd_id) {
        $cmd_id = (int)$cmd_id;
        /* Première requête avec les ID */
        $output = $this->_CONNECT->request('
            SELECT P.ref, P.label as nom, D.description as details, D.qty as qt
            FROM '.$this->prefix.'commandedet D, '.$this->prefix.'product P
            WHERE D.fk_commande = '.$cmd_id.'
            AND D.fk_product = P.rowid
        ', true);
        /* Deuxième requête sans les ID */
        $output = array_merge(
            $output,
            array_map(
                function ($entrie) {
                    $entrie['ref'] = '';
                    $entrie['nom'] = '';
                    return $entrie;
                },
                $this->_CONNECT->request('
                    SELECT D.description as details, D.qty as qt
                    FROM '.$this->prefix.'commandedet D
                    WHERE D.fk_commande = '.$cmd_id.'
                    AND D.fk_product IS NULL
                ', true)
            )
        );
        return $output;
    }

    public function getAuthor ($devis_id) {
        $devis_id = (int)$devis_id;
        /* Première requête avec les ID */
        $output = $this->_CONNECT->request('
            SELECT concat(firstname, \' \', lastname) as author, email, user_mobile
            FROM '.$this->prefix.'user
            WHERE rowid IN (
                SELECT fk_user_valid
                FROM '.$this->prefix.'propal
                WHERE rowid = '.$devis_id.'
            )
        ', true);
        return $output;
    }
}
