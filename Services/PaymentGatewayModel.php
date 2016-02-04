<?php
/**
 * @author		Can Berkol
 * @author		Said İmamoğlu
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com) (C) 2015
 * @license     GPLv3
 *
 * @date        23.12.2015
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
     * @var array
     */
    public $by_opts = array('entity', 'id', 'code', 'url_key', 'post');

    /**
     * PaymentGatewayModel constructor.
     *
     * @param object $kernel
     * @param string $db_connection
     * @param string $orm
     */
    public function __construct($kernel, string $db_connection = 'default', string $orm = 'doctrine') {
        parent::__construct($kernel, $db_connection, $orm);

        $this->entity = array(
            'pg' => array('name' => 'PaymentGatewayBundle:PaymentGateway', 'alias' => ''),
            'pgl' => array('name' => 'PaymentGatewayBundle:PaymentGatewayLocalization', 'alias' => 'pl'),
        );
    }

    /**
     * Destructor
     */
    public function __destruct() {
        foreach ($this as $property => $value) {
            $this->property = null;
        }
    }
}