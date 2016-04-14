<?php
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="payment_transaction",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     indexes={@ORM\Index(name="idxNPaymentTransactionDateAdded", columns={"date_added"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idxUPaymentTransactionId", columns={"id"})}
 * )
 */
class PaymentTransaction
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", length=10)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $transaction_id;

    /**
     * @ORM\Column(type="decimal", length=7, nullable=false, options={"default":0})
     */
    private $amount;

    /**
     * @ORM\Column(type="string", length=155, nullable=false)
     */
    private $status;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    private $response;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $date_added;

    /**
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\SiteManagementBundle\Entity\Site")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", onDelete="CASCADE")
     */
    private $site;

    /**
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\MemberManagementBundle\Entity\Member")
     * @ORM\JoinColumn(name="member", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $member;

    /**
     * @ORM\ManyToOne(targetEntity="PaymentGateway")
     * @ORM\JoinColumn(name="gateway", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $gateway;

    /**
     * @ORM\ManyToOne(targetEntity="ShoppingOrder")
     * @ORM\JoinColumn(name="shopping_order", referencedColumnName="id", onDelete="CASCADE")
     */
    private $shopping_order;
}