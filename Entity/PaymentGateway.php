<?php
/**
 * @name        PaymentGateway
 * @package		BiberLtd\Core\PaymentGatewayBundle
 *
 * @author		Murat Ünal
 *
 * @version     1.0.1
 * @date        11.10.2013
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Model / Entity class.
 *
 */
namespace BiberLtd\Core\Bundles\PaymentGatewayBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;
use BiberLtd\Core\CoreLocalizableEntity;
/** 
 * @ORM\Entity
 * @ORM\Table(
 *     name="payment_gateway",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     indexes={@ORM\Index(name="idx_n_payment_gateway_date_added", columns={"date_added"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idx_u_payment_gateway_id", columns={"id"})}
 * )
 */
class PaymentGateway extends CoreLocalizableEntity
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer", length=10)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** 
     * @ORM\Column(type="datetime", nullable=false)
     */
    public $date_added;

    /** 
     * @ORM\Column(type="text", nullable=false)
     */
    private $settings;

    /** 
     * @ORM\OneToMany(
     *     targetEntity="BiberLtd\Core\Bundles\PaymentGatewayBundle\Entity\PaymentGatewayLocalization",
     *     mappedBy="payment_gateway"
     * )
     */
    protected $localizations;

    /** 
     * @ORM\ManyToOne(targetEntity="BiberLtd\Core\Bundles\SiteManagementBundle\Entity\Site")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", onDelete="CASCADE")
     */
    private $site;
    /******************************************************************
     * PUBLIC SET AND GET FUNCTIONS                                   *
     ******************************************************************/

    /**
     * @name            getId()
     *  				Gets $id property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->id
     */
    public function getId(){
        return $this->id;
    }

    /**
     * @name                  setSettings ()
     *                                    Sets the settings property.
     *                                    Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $settings
     *
     * @return          object                $this
     */
    public function setSettings($settings) {
        if(!$this->setModified('settings', $settings)->isModified()) {
            return $this;
        }
		$this->settings = $settings;
		return $this;
    }

    /**
     * @name            getSettings ()
     *                              Returns the value of settings property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->settings
     */
    public function getSettings() {
        return $this->settings;
    }

    /**
     * @name                  setSite ()
     *                                Sets the site property.
     *                                Updates the data only if stored value and value to be set are different.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @use             $this->setModified()
     *
     * @param           mixed $site
     *
     * @return          object                $this
     */
    public function setSite($site) {
        if(!$this->setModified('site', $site)->isModified()) {
            return $this;
        }
		$this->site = $site;
		return $this;
    }

    /**
     * @name            getSite ()
     *                          Returns the value of site property.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed           $this->site
     */
    public function getSite() {
        return $this->site;
    }
}
/**
 * Change Log:
 * * ************************************
 * v1.0.1                      Murat Ünal
 * 11.10.2013
 * **************************************
 * D get_payment_transactions()
 * D set_payment_transactions()
 * **************************************
 * v1.0.0                      Murat Ünal
 * 23.09.2013
 * **************************************
 * A getDateAdded()
 * A getId()
 * A getLocalizations()
 * A getSettings()
 * A getSite()
 *
 * A setDateAdded()
 * A setLocalizations()
 * A setSettings()
 * A setSite()
 *
 */