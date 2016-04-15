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
use BiberLtd\Bundle\PaymentGatewayBundle\Entity as PGBEntity;
use BiberLtd\Bundle\MemberManagementBundle\Entity as MMBEntity;
use BiberLtd\Bundle\SiteManagementBundle\Entity as SMBEntity;
use BiberLtd\Bundle\SubscriptionManagementBundle\Entity as SMEntity;

/** Helper Models */
use BiberLtd\Bundle\SiteManagementBundle\Services as SMMService;
use BiberLtd\Bundle\MemberManagementBundle\Services as MMBService;
use BiberLtd\Bundle\PaymentGatewayBundle\Services as PGBService;
use BiberLtd\Bundle\ShoppingCartBundle\Services as SCService;
/** Core Service */
use BiberLtd\Bundle\CoreBundle\Services as CoreServices;
use BiberLtd\Bundle\CoreBundle\Exceptions as CoreExceptions;

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
            'payment_transaction' => array('name' => 'ShoppingCartBundle:PaymentTransaction', 'alias' => 'pt'),
            'subsciption_management' => array('name' => 'SubscriptionManagementBundle:Subscription', 'alias' => 'sm'),
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

    /**
     * @name 			deletePaymentTransaction()
     *  				Deletes an existing payment transaction from database.
     *
     * @since			1.0.1
     * @version         1.0.1
     * @author          Can Berkol
     *
     * @use             $this->deletePaymentTransactions()
     *
     * @param           mixed           $data             a single value of
     *                                                              id
     *                                                              code
     *                                                              Order entity
     *                                                              Gateway entity
     *                                                              Member entity
     *                                                              Site entity
     * @param           string          $by               'id', 'code', 'member', 'order', 'gateway', 'site'
     *
     * @return          mixed           $response
     */
    public function deletePaymentTransaction($data, $by = 'id') {
        return $this->deletePaymentTransactions(array($data), $by);
    }

    /**
     * @name 			deletePaymentTransactions()
     *  				Deletes provided payment transactions from database.
     *
     * @since			1.0.1
     * @version         1.0.1
     * @author          Can Berkol
     *
     * @use             $this->delete_entities()
     * @use             $this->createException()
     *
     * @param           array           $collection     Collection consists one of the following:
     *                                                              id
     *                                                              code
     *                                                              Order entity
     *                                                              Member entity
     *                                                              Gateway entity
     *                                                              Site entity
     * @param           string          $by             Accepts the following options: 'id', 'code', 'site', 'member', 'order', 'gateway', 'site'
     *
     * @return          array           $response
     */
    public function deletePaymentTransactions($collection, $by = 'id') {
        $this->resetResponse();
        $by_opts = array('id', 'code', 'gateway', 'member', 'order', 'site');
        if (!in_array($by, $by_opts)) {
            return $this->createException('InvalidParameterValueException', implode(',', $by_opts), 'err.invalid.parameter.collection');
        }
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterValueException', 'Array', 'err.invalid.parameter.collection');
        }
        /** If COLLECTION is ENTITYs then USE ENTITY MANAGER */
        if ($by == 'id') {
            $sub_response = $this->delete_entities($collection, 'BundleEntity\PaymentTransaction');
            /**
             * If there are items that cannot be deleted in the collection then $sub_Response['process']
             * will be equal to continue and we need to continue process; otherwise we can return response.
             */
            if ($sub_response['process'] == 'stop') {
                $this->response = array(
                    'rowCount' => $this->response['rowCount'],
                    'result' => array(
                        'set' => $sub_response['entries']['valid'],
                        'total_rows' => $sub_response['item_count'],
                        'last_insert_id' => null,
                    ),
                    'error' => false,
                    'code' => 'scc.db.delete.done',
                );

                return $this->response;
            } else {
                $collection = $sub_response['entries']['invalid'];
            }
        }

        /**
         * If COLLECTION is NOT Entitys OR MORE COMPLEX DELETION NEEDED
         * CREATE CUSTOM SQL / DQL
         *
         * If you need custom DELETE, you need to assign $q_str to well formed DQL string; otherwise use
         * $this>prepare_delete.
         */
        $table = $this->entity['payment_transaction']['name'] . ' ' . $this->entity['payment_transaction']['alias'];
        $q_str = $this->prepare_delete($table, $this->entity['payment_transaction']['alias'] . '.' . $by, $collection);

        $query = $this->em->createQuery($q_str);
        /**
         * 6. Run query
         */
        $query->getResult();
        /**
         * Prepare & Return Response
         */
        $collection_count = count($collection);
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $collection,
                'total_rows' => $collection_count,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.delete.done',
        );
        return $this->response;
    }

    /**
     * @name 			deletePaymentTransactionsOfGateway()
     *  				Deletes all coupons that belong to a site.
     *
     * @since			1.0.1
     * @version         1.0.1
     * @author          Can Berkol
     *
     * @use             $this->deletePaymentTransactions()
     *
     * @param           mixed           $gateway             entity or id.
     *
     * @return          mixed           $response
     */
    public function deletePaymentTransactionsOfGateway($gateway) {
        $this->resetResponse();
        /**
         * Parameter check
         */
        if (!$gateway instanceof PGBEntity\PaymentGateway && !is_integer($gateway)) {
            return $this->createException('InvalidParameterException', 'Site entity or integer', 'invalid.parameter.gateway');
        }
        $by = 'gateway';
        if ($gateway instanceof PGBEntity\PaymentGateway) {
            $gateway = $gateway->getId();
        }

        return $this->deletePaymentTransactions(array($gateway), $by);
    }

    /**
     * @name 			deletePaymentTransactionsOfMember()
     *  				Deletes all payment transactions that belong to a member.
     *
     * @since			1.0.1
     * @version         1.0.1
     * @author          Can Berkol
     *
     * @use             $this->deletePaymentTransactions()
     *
     * @param           mixed           $member          entity or id.
     *
     * @return          mixed           $response
     */
    public function deletePaymentTransactionsOfMember($member) {
        $this->resetResponse();
        /**
         * Parameter check
         */
        if (!$member instanceof MMBEntity\Member && !is_integer($member)) {
            return $this->createException('InvalidParameterException', 'Member entity or integer', 'invalid.parameter.member');
        }
        $by = 'member';
        if ($member instanceof MMBEntity\Member) {
            $member = $member->getId();
        }

        return $this->deletePaymentTransactions(array($member), $by);
    }

    /**
     * @name 			deletePaymentTransactionsOfOrder()
     *  				Deletes all payment transactions that belong to an order.
     *
     * @since			1.0.1
     * @version         1.0.1
     * @author          Can Berkol
     *
     * @use             $this->deletePaymentTransactions()
     *
     * @param           mixed           $order          entity or id.
     *
     * @return          mixed           $response
     */
    public function deletePaymentTransactionsOfOrder($order) {
        $this->resetResponse();
        /**
         * Parameter check
         */
        if (!$order instanceof BundleEntity\ShoppingOrder && !is_integer($order)) {
            return $this->createException('InvalidParameterException', 'ShoppingOrder entity or integer', 'invalid.parameter.order');
        }
        $by = 'order';
        if ($order instanceof BundleEntity\ShoppingOrder) {
            $order = $order->getId();
        }

        return $this->deletePaymentTransactions(array(ShoppingOrder), $by);
    }

    /**
     * @name 			deletePaymentTransactionsOfSite()
     *  				Deletes all payment transactions that belong to a site.
     *
     * @since			1.0.1
     * @version         1.0.1
     * @author          Can Berkol
     *
     * @use             $this->deletePaymentTransactions()
     *
     * @param           mixed           $site             entity or id.
     *
     * @return          mixed           $response
     */
    public function deletePaymentTransactionsOfSite($site) {
        $this->resetResponse();
        /**
         * Parameter check
         */
        if (!$site instanceof SMBEntity\Site && !is_integer($site)) {
            return $this->createException('InvalidParameterException', 'Site entity or integer', 'invalid.parameter.site');
        }
        $by = 'site';
        if ($site instanceof SMBEntity\Site) {
            $site = $site->getId();
        }

        return $this->deletePaymentGateways(array($site), $by);
    }

    /**
     * @name 			doesPaymentTransactionExist()
     *  				Checks if entry exists in database.
     *
     * @since			1.0.1
     * @version         1.0.1
     * @author          Can Berkol
     *
     * @use             $this->getPaymentTransaction()
     *
     * @param           mixed           $transaction    entity, id
     * @param           string          $by             all, entity, id
     * @param           bool            $bypass         If set to true does not return response but only the result.
     *
     * @return          mixed           $response
     */
    public function doesPaymentTransactionExist($transaction, $by = 'id', $bypass = false) {
        $this->resetResponse();
        $exist = false;

        $response = $this->getPaymentTransaction($transaction, $by);

        if (!$response['error'] && $response['result']['total_rows'] > 0) {
            $exist = true;
        }
        if ($bypass) {
            return $exist;
        }
        /**
         * Prepare & Return Response
         */
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $exist,
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name 			getPaymentTransaction()
     *  				Returns details of a payment transaction.
     *
     * @since			1.0.1
     * @version         1.0.1
     * @author          Can Berkol
     *
     * @use             $this->createException()
     * @use             $this->listPaymentTransactions()
     *
     * @param           mixed           $transaction        id
     * @param           string          $by                 entity, id, sku
     *
     * @return          mixed           $response
     */
    public function getPaymentTransaction($transaction, $by = 'id') {
        $this->resetResponse();
        $by_opts = array('id','shopping_order');
        if (!in_array($by, $by_opts)) {
            return $this->createException('InvalidParameterValueException', implode(',', $by_opts), 'err.invalid.parameter.by');
        }
        if (!is_object($transaction) && !is_numeric($transaction)) {
            return $this->createException('InvalidParameterException', 'PaymentTransaction', 'err.invalid.parameter.transaction');
        }
        if (is_object($transaction)) {
            if (!$transaction instanceof BundleEntity\PaymentTransaction) {
                return $this->createException('InvalidParameterException', 'PaymentTransaction', 'err.invalid.parameter.transaction');
            }
            /**
             * Prepare & Return Response
             */
            $this->response = array(
                'rowCount' => $this->response['rowCount'],
                'result' => array(
                    'set' => $transaction,
                    'total_rows' => 1,
                    'last_insert_id' => null,
                ),
                'error' => false,
                'code' => 'scc.db.entry.exist',
            );
            return $this->resetResponse();
        }
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['payment_transaction']['alias'] . '.' . $by, 'comparison' => '=', 'value' => $transaction),
                )
            )
        );

        $response = $this->listPaymentTransactions($filter, null, array('start' => 0, 'count' => 1));
        if ($response['error']) {
            return $response;
        }
        $collection = $response['result']['set'];
        /**
         * Prepare & Return Response
         */
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $collection[0],
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name 			insertPaymentTransaction()
     *  				Inserts one payment transaction into database.
     *
     * @since			1.0.1
     * @version         1.1.4
     * @author          Can Berkol
     * @author          Said İmamoğlu
     *
     * @use             $this->insertPaymentTransactions()
     *
     * @param           mixed           $transaction           Entity or post
     *
     * @return          array           $response
     */
    public function insertPaymentTransaction($transaction) {
        $this->resetResponse();
        return $this->insertPaymentTransactions(array($transaction));
    }

    /**
     * @name 			insertPaymentTransactions()
     *  				Inserts one or more payment transactions into database.
     *
     * @since			1.0.1
     * @version         1.1.4
     * @author          Can Berkol
     * @author          Said İmamoğlu
     *
     * @use             $this->createException()
     *
     * @param           array           $collection        Collection of entities or post data.
     *
     * @return          array           $response
     */
    public function insertPaymentTransactions($collection) {

        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameter', 'Array', 'err.invalid.parameter.collection');
        }
        $countInserts = 0;
        $insertedItems = array();
        foreach ($collection as $data) {
            if ($data instanceof BundleEntity\PaymentTransaction) {
                $entity = $data;
                $this->em->persist($entity);
                $insertedItems[] = $entity;
                $countInserts++;
            } else if (is_object($data)) {
                $entity = new BundleEntity\PaymentTransaction;
                if (isset($data->id)) {
                    unset($data->id);
                }
                if (!property_exists($data, 'date_added')) {
                    $data->date_created = new \DateTime('now', new \DateTimeZone($this->kernel->getContainer()->getParameter('app_timezone')));
                }
                foreach ($data as $column => $value) {
                    $set = 'set' . $this->translateColumnName($column);
                    switch ($column) {
                        case 'shopping_order':
                            $response = $this->getShoppingOrder($value, 'id');
                            if (!$response['error']) {
                                $entity->$set($response['result']['set']);
                            } else {
                                new CoreExceptions\EntityDoesNotExist($this->kernel, $value);
                            }
                            unset($response, $sModel);
                            break;
                        case 'member':
                            $memberModel = $this->kernel->getContainer()->get('membermanagement.model');
                            $response = $memberModel->getMember($value, 'id');
                            if (!$response['error']) {
                                $entity->$set($response['result']['set']);
                            } else {
                                new CoreExceptions\EntityDoesNotExist($this->kernel, $value);
                            }
                            unset($response, $sModel);
                            break;
                        case 'site':
                            $siteModel = $this->kernel->getContainer()->get('sitemanagement.model');
                            $response = $siteModel->getSite($value, 'id');
                            if (!$response['error']) {
                                $entity->$set($response['result']['set']);
                            } else {
                                new CoreExceptions\EntityDoesNotExist($this->kernel, $value);
                            }
                            unset($response, $sModel);
                            break;
                        case 'gateway':
                            $paymentGatewayModel = $this->kernel->getContainer()->get('paymentgateway.model');
                            $response = $paymentGatewayModel->getPaymentGateway($value, 'id');
                            if (!$response['error']) {
                                $entity->$set($response['result']['set']);
                            } else {
                                new CoreExceptions\EntityDoesNotExist($this->kernel, $value);
                            }
                            unset($response, $paymentGatewayModel);
                            break;
                        default:
                            $entity->$set($value);
                            break;
                    }
                }
                $this->em->persist($entity);
                $insertedItems[] = $entity;
                $countInserts++;
            } else {
                new CoreExceptions\InvalidDataException($this->kernel);
            }
        }
        if ($countInserts > 0) {
            $this->em->flush();
        }
        /**
         * Prepare & Return Response
         */
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $insertedItems,
                'total_rows' => $countInserts,
                'last_insert_id' => $entity->getId(),
            ),
            'error' => false,
            'code' => 'scc.db.insert.done',
        );
        return $this->response;
    }

    /**
     * @name 			listPaymentTransactions()
     *  				List payment transactions from database based on a variety of conditions.
     *
     * @since			1.0.1
     * @version         1.0.1
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $filter             Multi-dimensional array
     *
     *                                  Example:
     *                                  $filter[] = array(
     *                                              'glue' => 'and',
     *                                              'condition' => array(
     *                                                               array(
     *                                                                      'glue' => 'and',
     *                                                                      'condition' => array('column' => 'p.id', 'comparison' => 'in', 'value' => array(3,4,5,6)),
     *                                                                  )
     *                                                  )
     *                                              );
     *                                 $filter[] = array(
     *                                              'glue' => 'and',
     *                                              'condition' => array(
     *                                                              array(
     *                                                                      'glue' => 'or',
     *                                                                      'condition' => array('column' => 'p.status', 'comparison' => 'eq', 'value' => 'a'),
     *                                                              ),
     *                                                              array(
     *                                                                      'glue' => 'and',
     *                                                                      'condition' => array('column' => 'p.price', 'comparison' => '<', 'value' => 500),
     *                                                              ),
     *                                                             )
     *                                           );
     *
     *
     * @param           array           $sortorder              Array
     *                                      'column'            => 'asc|desc'
     * @param           array           $limit
     *                                      start
     *                                      count
     *
     * @param           string           $query_str             If a custom query string needs to be defined.
     *
     * @return          array           $response
     */
    private function listPaymentTransactions($filter = null, $sortorder = null, $limit = null, $query_str = null) {
        $this->resetResponse();
        if (!is_array($sortorder) && !is_null($sortorder)) {
            return $this->createException('InvalidSortOrderException', '', 'err.invalid.parameter.sortorder');
        }
        /**
         * Add filter checks to below to set join_needed to true.
         */
        /**         * ************************************************** */
        $order_str = '';
        $where_str = '';
        $group_str = '';
        $filter_str = '';

        /**
         * Start creating the query.
         *
         * Note that if no custom select query is provided we will use the below query as a start.
         */
        if (is_null($query_str)) {
            $query_str = 'SELECT ' . $this->entity['payment_transaction']['alias']
                . ' FROM ' . $this->entity['payment_transaction']['name'] . ' ' . $this->entity['payment_transaction']['alias'];
        }
        /**
         * Prepare ORDER BY section of query.
         */
        if ($sortorder != null) {
            foreach ($sortorder as $column => $direction) {
                switch ($column) {
                    case 'id':
                    case 'transaction_id':
                    case 'amount':
                    case 'date_added':
                        $column = $this->entity['payment_transaction']['alias'] . '.' . $column;
                        break;
                }
                $order_str .= ' ' . $column . ' ' . strtoupper($direction) . ', ';
            }
            $order_str = rtrim($order_str, ', ');
            $order_str = ' ORDER BY ' . $order_str . ' ';
        }

        /**
         * Prepare WHERE section of query.
         */
        if ($filter != null) {
            $filter_str = $this->prepare_where($filter);
            $where_str .= ' WHERE ' . $filter_str;
        }

        $query_str .= $where_str . $group_str . $order_str;

        $query = $this->em->createQuery($query_str);

        /**
         * Prepare LIMIT section of query
         */
        if ($limit != null) {
            if (isset($limit['start']) && isset($limit['count'])) {
                /** If limit is set */
                $query->setFirstResult($limit['start']);
                $query->setMaxResults($limit['count']);
            } else {
                new CoreExceptions\InvalidLimitException($this->kernel, '');
            }
        }
        /**
         * Prepare & Return Response
         */
        $result = $query->getResult();

        $total_rows = count($result);
        if ($total_rows < 1) {
            $this->response['code'] = 'err.db.entry.notexist';
            return $this->response;
        }
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $result,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name 			listPaymentTransactionsOfMember()
     *  				List payment transactions that belong to a specific member.
     *
     * @since			1.0.2
     * @version         1.0.2
     * @author          Can Berkol
     *
     * @use             $this->getProduct()
     *
     * @param           mixed           $member                 Member entity, id, e-mail, or username.
     * @param           array           $sortorder              Array
     *                                      'column'            => 'asc|desc'
     * @param           array           $limit
     *                                      start
     *                                      count
     *
     * @return          array           $response
     */
    public function listPaymentTransactionsOfMember($member, $sortorder = null, $limit = null) {
        $this->resetResponse();
        if (!$member instanceof MMBEntity\Member && !is_numeric($member) && !is_string($member)) {
            return $this->createException('InvalidParameterException', 'Member entity', 'err.invalid.parameter.member');
        }
        if (!is_object($member)) {
            $MMModel = new MMBService\MemberManagementModel($this->kernel, $this->db_connection, $this->orm);
            switch ($member) {
                case is_numeric($member):
                    $response = $MMModel->getMember($member, 'id');
                    break;
                case is_string($member):
                    $response = $MMModel->getMember($member, 'username');
                    if ($response['error']) {
                        $response = $MMModel->getMember($member, 'email');
                    }
                    break;
            }
            if ($response['error']) {
                return $this->createException('InvalidParameterException', 'Member entity', 'err.invalid.parameter.member');
            }
            $member = $response['result']['set'];
        }
        /**
         * Prepare $filter
         */
        $column = $this->entity['payment_transaction']['alias'] . '.member';
        $condition = array('column' => $column, 'comparison' => '=', 'value' => $member->getId());
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => $condition,
                )
            )
        );
        $response = $this->listPaymentTransactions($filter, $sortorder, $limit);

        if (!$response['error']) {
            return $response;
        }
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $response['result']['set'],
                'total_rows' => $response['result']['total_rows'],
                'last_insert_id' => null,
            ),
            'error' => true,
            'code' => 'err.db.entry.notexist',
        );
        return $this->response;
    }

    /**
     * @name 			listPaymentTransactionsOfOrder()
     *  				List payment transactions that belong to a specific order.
     *
     * @since			1.1.1
     * @version         1.1.1
     * @author          Can Berkol
     *
     * @use             $this->getProduct()
     *
     * @param           mixed           $order                  ShoppingOrder entity, id, e-mail, or username.
     * @param           array           $sortorder              Array
     *                                      'column'            => 'asc|desc'
     * @param           array           $limit
     *                                      start
     *                                      count
     *
     * @return          array           $response
     */
    public function listPaymentTransactionsOfOrder($order, $sortorder = null, $limit = null) {
        $this->resetResponse();
        if (!$order instanceof BundleEntity\ShoppingOrder && !is_numeric($order)) {
            return $this->createException('InvalidParameterException', 'ShoppingOrder entity or an integer as row id', 'err.invalid.parameter.order');
        }
        if (!is_object($order)) {
            switch ($order) {
                case is_numeric($order):
                    $response = $this->getShoppingOrder($order, 'id');
                    break;
            }
            if ($response['error']) {
                return $this->createException('InvalidParameterException', 'Member entity', 'err.invalid.parameter.member');
            }
            $order = $response['result']['set'];
        }
        /**
         * Prepare $filter
         */
        $column = $this->entity['payment_transaction']['alias'] . '.shopping_order';
        $condition = array('column' => $column, 'comparison' => '=', 'value' => $order->getId());
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => $condition,
                )
            )
        );
        $response = $this->listPaymentTransactions($filter, $sortorder, $limit);

        if (!$response['error']) {
            return $response;
        }
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $response['result']['set'],
                'total_rows' => $response['result']['total_rows'],
                'last_insert_id' => null,
            ),
            'error' => true,
            'code' => 'err.db.entry.notexist',
        );
        return $this->response;
    }

    /**
     * @name 			updatePaymentTransaction()
     *  				Updates single payment transaction. The data must be either a post data (array) or an entity
     *
     * @since			1.0.2
     * @version         1.0.2
     * @author          Can Berkol
     *
     * @use             $this->updatePaymentTransactions()
     *
     * @param           mixed           $data           entity or post data
     * @param           string          $by             entity, post
     *
     * @return          mixed           $response
     */
    public function updatePaymentTransaction($data, $by = 'post') {
        return $this->updatePaymentTransactions(array($data), $by);
    }

    /**
     * @name 			updatePaymentTransactions()
     *  				Updates one or more payment transactions details in database.
     *
     * @since			1.0.2
     * @version         1.0.2
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $collection      Collection of Product entities or array of entity details.
     * @param           array           $by              entity, post
     *
     * @return          array           $response
     */
    public function updatePaymentTransactions($collection, $by = 'post') {
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
        }
        $by_opts = array('id', 'post');
        if (!in_array($by, $by_opts)) {
            return $this->createException('InvalidParameterException', implode(',', $by_opts), 'err.invalid.parameter.by');
        }
        if ($by == 'id') {
            $sub_response = $this->update_entities($collection, 'BundleEntity\\PaymentTransaction');
            /**
             * If there are items that cannot be deleted in the collection then $sub_Response['process']
             * will be equal to continue and we need to continue process; otherwise we can return response.
             */
            if ($sub_response['process'] == 'stop') {
                $this->response = array(
                    'rowCount' => $this->response['rowCount'],
                    'result' => array(
                        'set' => $sub_response['entries']['valid'],
                        'total_rows' => $sub_response['item_count'],
                        'last_insert_id' => null,
                    ),
                    'error' => false,
                    'code' => 'scc.db.delete.done',
                );
                return $this->response;
            } else {
                $collection = $sub_response['entries']['invalid'];
            }
        }
        /**
         * If by post
         */
        $to_update = array();
        $count = 0;
        $collection_by_id = array();
        foreach ($collection as $item) {
            if (!isset($item['id'])) {
                unset($collection[$count]);
            }
            $to_update[] = $item['id'];
            $collection_by_id[$item['id']] = $item;
            $count++;
        }
        unset($collection);
        $filter = array(
            array(
                'glue' => 'and',
                'condition' => array(
                    array(
                        'glue' => 'and',
                        'condition' => array('column' => $this->entity['payment_transaction']['alias'] . '.id', 'comparison' => 'in', 'value' => $to_update),
                    )
                )
            )
        );
        $response = $this->listProducts($filter);
        if ($response['error']) {
            return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
        }
        $entities = $response['result']['set'];
        foreach ($entities as $entity) {
            $data = $collection_by_id[$entity->getId()];
            /** Prepare foreign key data for process */
            $site = '';
            if (isset($data['site'])) {
                $site = $data['site'];
            }
            unset($data['site']);

            $order = '';
            if (isset($data['order'])) {
                $order = $data['order'];
            }
            unset($data['order']);

            $gateway = '';
            if (isset($data['gateway'])) {
                $gateway = $data['gateway'];
            }
            unset($data['gateway']);

            $member = '';
            if (isset($data['member'])) {
                $member = $data['member'];
            }
            unset($data['member']);

            foreach ($data as $column => $value) {
                $method_set = 'set_' . $column;
                $method_get = 'get_' . $column;
                /**
                 * Set the value only if there is a corresponding value in collection and if that value is different
                 * from the one set in database
                 */
                if (isset($collection_by_id[$entity->getId()][$column]) && $collection_by_id[$entity->getId()][$column] != $entity->$method_get()) {
                    $entity->$method_set($value);
                }

                /** HANDLE FOREIGN DATA :: SITE */
                if (is_numeric($site)) {
                    $SMModel = new SMMService\SiteManagementModel($this->kernel, $this->db_connection, $this->orm);
                    $response = $SMModel->getSite($site, 'id');
                    if ($response['error']) {
                        new CoreExceptions\InvalidSiteException($this->kernel, $value);
                        break;
                    }
                    $site_entity = $response['result']['set'];
                    $entity->$method_set($site_entity);
                    /** Free up some memory */
                    unset($site, $response, $SMModel, $site_Entity);
                }
                /** HANDLE FOREIGN DATA :: MEMBER */
                if (is_numeric($member)) {
                    $MMModel = new MMBService\MemberManagementModel($this->kernel, $this->db_connection, $this->orm);
                    $response = $MMModel->getMember($member, 'id');
                    if ($response['error']) {
                        new CoreExceptions\InvalidSiteException($this->kernel, $value);
                        break;
                    }
                    $member_entity = $response['result']['set'];
                    $entity->$method_set($member_entity);
                    /** Free up some memory */
                    unset($member, $response, $MMModel, $member_entity);
                }
                /** HANDLE FOREIGN DATA :: GATEWAY */
                if (is_numeric($gateway)) {
                    $PGBModel = new PGBService\PaymentGatewayModel($this->kernel, $this->db_connection, $this->orm);
                    $response = $MMModel->getPaymentGateway($gateway, 'id');
                    if ($response['error']) {
                        new CoreExceptions\InvalidSiteException($this->kernel, $value);
                        break;
                    }
                    $gateway_entity = $response['result']['set'];
                    $entity->$method_set($gateway_entity);
                    /** Free up some memory */
                    unset($gateway, $response, $PGBModel, $gateway_entity);
                }
                /** HANDLE FOREIGN DATA :: ORDER */
                if (is_numeric($order)) {
                    $response = $this->getShoppingOrder($order, 'id');
                    if ($response['error']) {
                        new CoreExceptions\InvalidSiteException($this->kernel, $value);
                        break;
                    }
                    $order_entity = $response['result']['set'];
                    $entity->$method_set($order_entity);
                    /** Free up some memory */
                    unset($order, $response, $order_entity);
                }
                $this->em->persist($entity);
            }
        }
        $this->em->flush();

        $total_rows = count($to_update);
        /**
         * Prepare & Return Response
         */
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $to_update,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.update.done',
        );
        return $this->response;
    }


    public function listShoppingOrderItemsOfSubscription($subscription,$filter = null, $sortorder = null, $limit = null, $query_str = null) {
        if ($subscription instanceof SMEntity\Subscription) {
            $subscription = $subscription->getId();
        }elseif($subscription instanceof \stdClass){
            $subscription = $subscription->id;
        }elseif(is_int($subscription)){
            $subscription = $subscription;
        }else{
            return $this->createException('InvalidParameter', 'Subscription', 'err.invalid.parameter');
        }
        $filter = array();
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['subsciption_management']['alias'] . '.subscription', 'comparison' => '=', 'value' => $subscription),
                )
            )
        );
        $scModel =  new SCService\ShoppingCartModel($this->kernel, $this->dbConnection, $this->orm);
        return $scModel->listShoppingOrderItems($filter);
    }

}