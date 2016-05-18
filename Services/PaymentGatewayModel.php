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
use BiberLtd\Bundle\CoreBundle\Responses\ModelResponse;
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
        $response = $this->listPaymentGateways($filter, null, array('start' => 0, 'count' => 1));
        if ($response->error->exist) {
            return $response;
        }
        $response->stats->execution->start = $timeStamp;
        $response->stats->execution->end = microtime(true);
        $response->result->set = $response->result->set[0];

        return $response;
    }
    /**
     * @param array|null $filter
     * @param array|null $sortOrder
     * @param array|null $limit
     *
     * @return array|\BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function listPaymentGateways(array $filter = null, array $sortOrder = null, array$limit = null)
    {
        $timeStamp = microtime(true);
        if (!is_array($sortOrder) && !is_null($sortOrder)) {
            return $this->createException('InvalidSortOrderException', '$sortOrder must be an array with key => value pairs where value can only be "asc" or "desc".', 'E:S:002');
        }
        $oStr = $wStr = $gStr = $fStr = '';

        $qStr = 'SELECT ' . $this->entity['pgl']['alias'] .', '. $this->entity['pg']['alias']
            . ' FROM ' . $this->entity['pgl']['name'] . ' ' . $this->entity['pgl']['alias']
            . ' JOIN ' . $this->entity['pgl']['alias'] . '.payment_gateway  ' . $this->entity['pg']['alias'];

        if (!is_null($sortOrder)) {
            foreach ($sortOrder as $column => $direction) {
                switch ($column) {
                    case 'id':
                    case 'date_added':
                    case 'site':
                        $column = $this->entity['pg']['alias'] . '.' . $column;
                        break;
                    case 'name':
                    case 'description':
                    case 'url_key':
                        $column = $this->entity['pgl']['alias'] . '.' . $column;
                        break;
                }
                $oStr .= ' ' . $column . ' ' . strtoupper($direction) . ', ';
            }
            $oStr = rtrim($oStr, ', ');
            $oStr = ' ORDER BY ' . $oStr . ' ';
        }

        if (!is_null($filter)) {
            $fStr = $this->prepareWhere($filter);
            $wStr .= ' WHERE ' . $fStr;
        }

        $qStr .= $wStr . $gStr . $oStr;
        $q = $this->em->createQuery($qStr);
        $q = $this->addLimit($q, $limit);

        $result = $q->getResult();

        $entities = [];
        foreach ($result as $entry) {
            /**
             * @var \BiberLtd\Bundle\PaymentGatewayBundle\Entity\PaymentGatewayLocalization $entry
             */
            $id = $entry->getPaymentGateway()->getId();
            if (!isset($unique[$id])) {
                $unique[$id] = '';
                $entities[] = $entry->getPaymentGateway();
            }
        }
        $totalRows = count($entities);
        if ($totalRows < 1) {
            return new ModelResponse(null, 0, 0, null, true, 'E:D:002', 'No entries found in database that matches to your criterion.', $timeStamp, microtime(true));
        }
        return new ModelResponse($entities, $totalRows, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, microtime(true));
    }
}