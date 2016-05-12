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
     * PaymentGatewayModel constructor.
     *
     * @param object $kernel
     * @param string $db_connection
     * @param string $orm
     */
    public function __construct($kernel, string $db_connection = 'default', string $orm = 'doctrine') {
        parent::__construct($kernel, $db_connection, $orm);

        $this->entity = array(
            'pg' => array('name' => 'PaymentGatewayBundle:PaymentGateway', 'alias' => 'pg'),
            'pgl' => array('name' => 'PaymentGatewayBundle:PaymentGatewayLocalization', 'alias' => 'pgl'),
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

    /**
     * @param $gateway
     * @return ModelResponse
     */
    public function getPaymentGateway($gateway)
    {
        $timeStamp = microtime(true);
        if ($gateway instanceof BundleEntity\PaymentGateway) {
            return new ModelResponse($gateway, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime(true));
        }
        $result = null;
        switch ($gateway) {
            case is_numeric($gateway):
                $result = $this->em->getRepository($this->entity['pg']['name'])->findOneBy(array('id' => $gateway));
                break;
            case is_string($gateway):
                $response = $this->getPaymentGatewayByUrlKey($gateway);
                if (!$response->error->exist) {
                    $result = $response->result->set;
                }
                unset($response);
                break;
        }
        if (is_null($result)) {
            return new ModelResponse($result, 0, 0, null, true, 'E:D:002', 'Unable to find request entry in database.', $timeStamp, microtime(true));
        }

        return new ModelResponse($result, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime(true));
    }

    /**
     * @param string $urlKey
     * @param null $language
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function getPaymentGatewayByUrlKey(string $urlKey, $language = null)
    {
        $timeStamp = microtime(true);
        if (!is_string($urlKey)) {
            return $this->createException('InvalidParameterValueException', '$urlKey must be a string.', 'E:S:007');
        }
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['pgl']['alias'] . '.url_key', 'comparison' => '=', 'value' => $urlKey),
                )
            )
        );
        if (!is_null($language)) {
            $mModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
            $response = $mModel->getLanguage($language);
            if (!$response->error->exist) {
                $filter[] = array(
                    'glue' => 'and',
                    'condition' => array(
                        array(
                            'glue' => 'and',
                            'condition' => array('column' => $this->entity['pgl']['alias'] . '.language', 'comparison' => '=', 'value' => $response->result->set->getId()),
                        )
                    )
                );
            }
        }
        $response = $this->listProducts($filter, null, array('start' => 0, 'count' => 1));
        if ($response->error->exist) {
            return $response;
        }
        $response->stats->execution->start = $timeStamp;
        $response->stats->execution->end = microtime(true);
        $response->result->set = $response->result->set[0];

        return $response;
    }
}