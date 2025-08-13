<?php
require_once(dirname(__FILE__).'/dolibarr.db.php');
require_once(dirname(__FILE__).'/../../conf/conf.php');

class FreshprocessApi {
    public static function init () {
        if(isset($_GET['action']) && $_GET['action'] == 'crontab') {
            self::sync();
        }
    }

    private static function apiRequest($host, $method, $keys, $data=array()) {
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $host);
        curl_setopt($handle, CURLOPT_USERAGENT, 'FreshProcess_dolibarr:1.0');
        curl_setopt($handle, CURLOPT_USERPWD, $keys->public . ":" . $keys->private);

        /* Envoyer des données */
        if($method == 'POST') {
            curl_setopt($handle, CURLOPT_POST, 1);
            curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($handle, CURLOPT_HTTPHEADER,
                array('Content-Type: application/json')
            );
        }

        /* Mettre à jour les données */
        if($method == 'PUT') {
            curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($handle, CURLOPT_HTTPHEADER,
                array('Content-Type: application/json')
            );
        }

        /* Supprimer des données */
        if($method == 'DELETE') {
            curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        /* Retourner les données */
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($handle);
        if (curl_error($handle)) {
            echo '<xmp>'.print_r(curl_error($handle), 1).'</xmp>';
        }
        curl_close($handle);

        return $output;
    }

    private static function sync () {
        global $dolibarr_main_db_prefix,
            $dolibarr_main_db_name, $dolibarr_main_db_host,
            $dolibarr_main_db_user, $dolibarr_main_db_pass;

        /* Choix du mode de connexion */
        $mode = "DEVIS"; // DEVIS ou COMMANDE

        /* Configuration */
        $config = self::getConfig();
        if($config === false) {
            echo '<p><b>[ERROR] Your settings are wrong.</b></p>';die();
        }

        /* Connexion MultiPDO */
        $db = new DolibarrBase((object)array(
            'host' => $dolibarr_main_db_host,
            'base' => $dolibarr_main_db_name,
            'user' => $dolibarr_main_db_user,
            'pass' => $dolibarr_main_db_pass
        ), $dolibarr_main_db_prefix);

        /* Transformer les devis en projet et les regrouper par API */
        $projets = array_reduce(
            $mode == 'DEVIS' ? $db->getDevis() : $db->getCmd(),
            function ($accum, $devis) use($db, $config, $mode) {
                $devis = (object)$devis;
                $groupeTaches = preg_replace("/(^[\\s]+|[^\\x00-\\x1F\\x7F\\w ]+|[\\s]+$)/u", '', $devis->ref_client);
                /* Identifier l'API responsable */
                $api = 0;
                foreach($config->keys as $index => $key) {
                    if(in_array($groupeTaches, $key->ref_client)) {
                        $api = $index + 1;
                        break;
                    }
                }
                if($api) {
                    /* Données */
                    $ref = $devis->ref;
                    $totalHt = (float)$devis->total_ht;
                    $codeClient = $mode == 'DEVIS' ? $db->getCodeClient($devis->rowid) : $db->getCodeClientCmd($devis->rowid);
                    $societe = $db->getSocieteName($devis->fk_soc);
                    $nomProjet = $mode == 'DEVIS' ? $db->getNameProject($devis->rowid) : $db->getNameProjectCmd($devis->rowid);
                    $lienDevis = self::getLienDolibarr($devis->rowid, $mode);
                    $dateCreation = $mode == 'DEVIS' ? strtotime($devis->datec) : strtotime($devis->date_creation);
                    $dateCloture = (!strtotime($devis->date_cloture) ? $dateCreation : strtotime($devis->date_cloture));
                    $dateDebut = $dateCloture;
                    $dateFin = strtotime($devis->date_livraison);
                    $produits = $mode == 'DEVIS' ? $db->getProducts($devis->rowid) : $db->getProductsCmd($devis->rowid);
                    $notePrivate = html_entity_decode($devis->note_private);
                    $notePublic = html_entity_decode($devis->note_public);
                    /* (1) Contact de suivi */
                    $email = $tel = '';
                    $ctSuivi = $mode == 'DEVIS'
                        ? $db->getContactsPropal($devis->rowid, 'suivi')
                        : $db->getContactsCmd($devis->rowid, 'suivi');
                    if(is_array($ctSuivi) && isset($ctSuivi[0])) {
                        $email = (isset($ctSuivi[0]['email']) ? $ctSuivi[0]['email'] : '');
                        $tel = (isset($ctSuivi[0]['phone']) ? $ctSuivi[0]['phone'] : '');
                    }
                    /* (2) Contact de livraison */
                    $adresse = '';
                    $ctLivraison = $mode == 'DEVIS'
                        ? $db->getContactsPropal($devis->rowid, 'livraison')
                        : $db->getContactsCmd($devis->rowid, 'livraison');
                    if(is_array($ctLivraison) && isset($ctLivraison[0])) {
                        $adresse = (isset($ctLivraison[0]['address_c']) ? $ctLivraison[0]['address_c'] : '');
                    }
                    /* (3) Contact commercial */
                    $emailCom = $telCom = '';
                    $ctCom = $mode == 'DEVIS'
                        ? $db->getAuthor($devis->rowid) // pour le devis on choisit plutôt l'auteur
                        : $db->getContactsCmd($devis->rowid, 'commercial');
                    if(is_array($ctCom) && isset($ctCom[0])) {
                        $emailCom = (isset($ctCom[0]['email']) ? $ctCom[0]['email'] : '');
                        $telCom = (isset($ctCom[0]['phone']) ? $ctCom[0]['phone'] : '');
                    }
                    /* Formatage des dates */
                    $dateDebut = gmdate('Y-m-d\\TH:i:s.000\\Z', $dateDebut);
                    $dateFin = gmdate('Y-m-d\\TH:i:s.000\\Z', $dateFin);
                    $dateCreation = gmdate('Y-m-d\\TH:i:s.000\\Z', $dateCreation);
                    /* Correctifs en tout genre */
                    if(!$nomProjet) {
                        $nomProjet = $ref;
                    }
                    /* Créer l'emplacement */
                    if(!isset($accum[$api-1])) {
                        $accum[$api-1] = array();
                    }
                    /* Ajouter l'objet formaté */
                    array_push(
                        $accum[$api-1],
                        (object)array(
                            'nom' => $nomProjet,
                            'ref' => $ref,
                            'codeClient' => $codeClient,
                            'groupeTaches' => $groupeTaches,
                            'lienDevis' => $lienDevis,
                            'nomClient' => $societe,
                            'email' => $email,
                            'tel' => $tel,
                            'dateDebut' => $dateDebut,
                            'dateFin' => $dateFin,
                            'dateCreation' => $dateCreation,
                            'prixHT' => $totalHt,
                            'produits' => $produits,
                            'notes' => (
                                !empty($notePublic) && !empty($notePrivate)
                                ? array( 'Note privée : '.$notePrivate, 'Note publique : '.$notePublic )
                                : (
                                    !empty($notePublic)
                                    ? array( 'Note publique : '.$notePublic )
                                    : (
                                        !empty($notePrivate)
                                        ? array( 'Note privée : '.$notePrivate )
                                        : array()
                                    )
                                )
                            ),
                            'adresse' => $adresse,
                            'emailCom' => $emailCom,
                            'telCom' => $telCom,
                        )
                    );
                }
                return $accum;
            },
            array()
        );

        /* Parser chacune des API */
        foreach($config->keys as $index => $keyAccess) {
            /* Pas de projet pour cette API */
            if(!isset($projets[$index])) { continue; }
            /* Récupérer la liste des références enregistrées */
            $listIdRef = json_decode(self::apiRequest($config->api, 'GET', $keyAccess));
            if(
                is_object($listIdRef) &&
                isset($listIdRef->success) &&
                isset($listIdRef->data) &&
                is_array($listIdRef->data) &&
                $listIdRef->success
            ) {
                /* Filtrer les références existantes */
                $listCreate = $listUpdate = $listDelete = array();
                if(count($listIdRef->data)) {
                    foreach($projets[$index] as $projet) {
                        if(!in_array($projet->ref, $listIdRef->data)) {
                            /* Create mode */
                            array_push($listCreate, $projet);
                        } else {
                            /* Update mode */
                            array_push($listUpdate, $projet);
                            /* Delete mode */
                            array_splice(
                                $listIdRef->data,
                                array_search($projet->ref, $listIdRef->data),
                                1
                            );
                        }
                        /* liste d'ID permettant d'archiver les projets */
                        $listDelete = $listIdRef->data;
                    }
                } else {
                    foreach($projets[$index] as $projet) {
                        array_push($listCreate, $projet);
                    }
                }
                /* [PUT] Mettre à jour les références */
                if(count($listUpdate)) {
                    $req = json_decode(self::apiRequest($config->api, 'PUT', $keyAccess, $listUpdate));
                    if(
                        is_object($req) &&
                        isset($req->success) &&
                        $req->success
                    ) {
                        echo '<p><b>[SUCCESS]</b> With the key "'.$keyAccess->name.'" the projects has been updated.<br/>';
                        foreach($listUpdate as $item) {
                            echo $item->ref . ' - [' . $item->groupeTaches . ']<br/>';
                        }
                        echo '</p>';
                    } else {
                        echo '<p><b>[WARN]</b> The key "'.$keyAccess->name.'" can\'t update the projects.</p>';
                    }
                }
                /* [DELETE] Enlever les références */
                foreach($listDelete as $idRef) {
                    if($mode === 'DEVIS' && !preg_match('#^PR#', $idRef)) continue;
                    if($mode === 'COMMANDE' && !preg_match('#^CO#', $idRef)) continue;
                    $req = json_decode(self::apiRequest($config->api.'/'.$idRef, 'DELETE', $keyAccess));
                    if(
                        is_object($req) &&
                        isset($req->success) &&
                        $req->success
                    ) {
                        echo '<p><b>[SUCCESS]</b> With the key "'.$keyAccess->name.'" the project "'.$idRef.'" has been archived.</p>';
                    }
                }
                /* [CREATE] Ajouter des références */
                $req = json_decode(self::apiRequest($config->api, 'POST', $keyAccess, $listCreate));
                if(
                    is_object($req) &&
                    isset($req->success) &&
                    $req->success
                ) {
                    echo '<p><b>[SUCCESS]</b> The key "'.$keyAccess->name.'" is synchronized with Freshprocess.<br/>';
                    foreach($listCreate as $item) {
                        echo $item->ref . '<br/>';
                    }
                    echo '</p>';
                } else {
                    echo '<p><b>[WARN]</b> The key "'.$keyAccess->name.'" can\'t sent the projects.</p>';
                }
            } else {
                echo '<p><b>[WARN]</b> The key "'.$keyAccess->name.'" is wrong or the server is can\'t reached.</p>';
            }
        }
    }

    private static function getConfig () {
        /* Récupération des données */
        $config = json_decode(
            @file_get_contents(
                dirname(__FILE__).'/../config/api.config.json',
                false
            )
        );

        /* Validation */
        if(
            isset($config->api) &&
            isset($config->keys) &&
            is_string($config->api) &&
            is_array($config->keys)
        ) {
            return (object)array(
                "api" => $config->api,
                "keys" => array_reduce(
                    $config->keys,
                    function ($accum, $keyAccess) {
                        if(
                            /* Accessible */
                            isset($keyAccess->name) &&
                            isset($keyAccess->public) &&
                            isset($keyAccess->private) &&
                            isset($keyAccess->ref_client) &&
                            /* Dans le bon format */
                            is_string($keyAccess->name) &&
                            is_string($keyAccess->public) &&
                            is_string($keyAccess->private) &&
                            is_array($keyAccess->ref_client) &&
                            /* Non vide */
                            strlen($keyAccess->name) &&
                            strlen($keyAccess->public) &&
                            strlen($keyAccess->private) &&
                            count($keyAccess->ref_client)
                        ) {
                            array_push($accum, (object)array(
                                'name' => $keyAccess->name,
                                'public' => $keyAccess->public,
                                'private' => $keyAccess->private,
                                'ref_client' => array_map(
                                    function ($ref) {
                                        return preg_replace("/(^[\\s]+|[^\\x00-\\x1F\\x7F\\w ]+|[\\s]+$)/u", "", $ref);
                                    },
                                    $keyAccess->ref_client
                                )
                            ));
                        }
                        return $accum;
                    },
                    array()
                )
            );
        }
        return false;
    }

    /* Récupérer le lien vers Dolibarr */
    private static function getLienDolibarr ($id=0, $mode='DEVIS') {
        return implode('', array(
            $_SERVER['REQUEST_SCHEME'].'://',
            $_SERVER['SERVER_NAME'],
            implode('/', array_slice(explode('/', $_SERVER['REQUEST_URI']), 0, -2)).
            ($mode === 'DEVIS' ? '/comm/propal/card.php?id=' : '/commande/card.php?id=').$id
        ));
    }
}

FreshprocessApi::init();
