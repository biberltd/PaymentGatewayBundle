<?php

/**
 * PaymentGatewayModel Class
 *
 * This class acts as a database proxy model for PaymentGatewayModelBundle functionalities.
 * 
 * 
 * @package	Core\Bundles\PaymentGatewayBundle
 * @subpackage	Services
 * @name	PaymentGatewayModel
 *
 * @author	Said Imamoglu
 * @author      Said Imamoglu
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.0
 * @date        28.11.2013
 *
 * =============================================================================================================
 * !! INSTRUCTIONS ON IMPORTANT ASPECTS OF MODEL METHODS !!!
 *
 * Each model function must return a $response ARRAY.
 * The array must contain the following keys and corresponding values.
 *
 * $response = array(
 *              'result'    =>   An array that contains the following keys:
 *                               'set'         Actual result set returned from ORM or null
 *                               'total_rows'  0 or number of total rows
 *                               'last_insert_id' The id of the item that is added last (if insert action)
 *              'error'     =>   true if there is an error; false if there is none.
 *              'code'      =>   null or a semantic and short English string that defines the error concanated
 *                               with dots, prefixed with err and the initials of the name of model class.
 *                               EXAMPLE: err.amm.action.not.found success messages have a prefix called scc..
 *
 *                               NOTE: DO NOT FORGET TO ADD AN ENTRY FOR ERROR CODE IN BUNDLE'S
 *                               RESOURCES/TRANSLATIONS FOLDER FOR EACH LANGUAGE.
 * =============================================================================================================   
 *
 */

namespace BiberLtd\Bundle\PaymentGatewayBundle\Services;

/** Extends CoreModel */
use BiberLtd\Bundle\CoreBundle\CoreModel;
/** Entities to be used */
use BiberLtd\Bundle\PaymentGatewayBundle\Entity as BundleEntity;
/** Helper Models */
use BiberLtd\Bundle\SiteManagementBundle\Services as SMMService;
/** Core Service */
use BiberLtd\Bundle\CoreBundle\Services as CoreServices;

class PaymentGatewayModel extends CoreModel {
    /**
     * @name            __construct()
     *                  Constructor.
     *
     * @author          Said Imamoglu
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @param           object          $kernel
     * @param           string          $db_connection  Database connection key as set in app/config.yml
     * @param           string          $orm            ORM that is used.
     */

    /** @var $by_opitons handles by options */
    public $by_opts = array('entity', 'id', 'code', 'url_key', 'post');

    public function __construct($kernel, $db_connection = 'default', $orm = 'doctrine') {
        parent::__construct($kernel, $db_connection, $orm);

        /**
         * Register entity names for easy reference.
         */
        $this->entity = array(
            'file' => array('name' => 'PaymentGatewayBundle:PaymentGateway', 'alias' => ''),
            'file_upload_folder' => array('name' => 'PaymentGatewayBundle:PaymentGatewayLocalization', 'alias' => 'pl'),
        );
    }

    /**
     * @name            __destruct()
     *                  Destructor.
     *
     * @author          Said Imamoglu
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     */
    public function __destruct() {
        foreach ($this as $property => $value) {
            $this->property = null;
        }
    }
}