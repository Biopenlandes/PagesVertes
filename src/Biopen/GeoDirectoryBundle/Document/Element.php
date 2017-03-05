<?php

/**
 * This file is part of the MonVoisinFaitDuBio project.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2016 Sebastian Castro - 90scastro@gmail.com
 * @license    MIT License
 * @Last Modified time: 2017-03-05 09:53:05
 */
 

namespace Biopen\GeoDirectoryBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * Element
 *
 * @MongoDB\Document(repositoryClass="Biopen\GeoDirectoryBundle\Repository\ElementRepository")
 */
class Element
{
    /**
     * @var int
     *
     * @MongoDB\Id(strategy="ALNUM") 
     */
    private $id;

    /**
     * @var string
     *
     * @MongoDB\Field(type="string")
     */
    private $name;

    /**
     * 
     *
     * @MongoDB\Field(type="float")
     */
    private $lat;

    /**
     * 
     *
     * @MongoDB\Field(type="float")
     */
    private $lng;

    /**
     * @var string
     *
     * @MongoDB\Field(type="string")
     */
    private $address;

    /**
     * @var string
     *
     * @MongoDB\Field(type="string")
     */
    private $postalCode;

    /**
     * @var string
     *
     * @MongoDB\Field(type="text", nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @MongoDB\Field(type="string")
     */
    private $tel;

    /**
     * @var string
     *
     * @MongoDB\Field(type="string")
     */
    private $mail;

    /**
     * @var string
     *
     * @MongoDB\Field(type="string")
     */
    private $webSite;
    
    /**
     * @var \stdClass
     *
     * @MongoDB\Field(type="object", nullable=false)
     */
    private $categories;

    /**
     * @var \stdClass
     *
     * @MongoDB\Field(type="object", nullable=true)
     */
    private $openHours;

    /**
     * @var string
     *
     * @MongoDB\Field(type="string", nullable=true)
     */
    private $openHoursMoreInfos;

    /**
     * @var string
     *
     * @MongoDB\Field(type="string")
     */
    private $contributor;

   /**
     * @var string
     *
     * @MongoDB\Field(type="string")
     */
    private $contributorMail;

   /**
     * @var string
     *
     * @MongoDB\Field(type="string")
     */
    private $validationCode;

    /**
     * @var bool
     *
     * MongoDB\Field(type="boolean")
     */
    private $valide = false;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
        $this->validationCode = md5(uniqid(rand(), true));
        $this->contributor = '';
    }

    public function reinitContributor()
    {
        $this->validationCode = md5(uniqid(rand(), true));
        $this->contributorMail = '';
        $this->contributor = '';
    }

    public function resetProducts()
    {
        $this->productsCopy = new \Doctrine\Common\Collections\ArrayCollection();
        $this->products->clear();
    }

    private function calculateWastedDistance()
    {
        if ( count($this->getProducts()) == 0 || in_array($this->getType(), array("epicerie","marche","boutique") )) return $this->getDistance();
        //$waste = 1.0 / pow(count($this->getProducts()),2);
        $waste = -1.0*count($this->getProducts())/10.0 + 1.0;
        return $this->getDistance() * $waste;
    }

 

    /**
     * Get id
     *
     * @return custom_id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set lat
     *
     * @param float $lat
     * @return $this
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
        return $this;
    }

    /**
     * Get lat
     *
     * @return float $lat
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param float $lng
     * @return $this
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
        return $this;
    }

    /**
     * Get lng
     *
     * @return float $lng
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * Get address
     *
     * @return string $address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set postalCode
     *
     * @param string $postalCode
     * @return $this
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    /**
     * Get postalCode
     *
     * @return string $postalCode
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set description
     *
     * @param text $description
     * @return $this
     */
    public function setDescription(\text $description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return text $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set tel
     *
     * @param string $tel
     * @return $this
     */
    public function setTel($tel)
    {
        $this->tel = $tel;
        return $this;
    }

    /**
     * Get tel
     *
     * @return string $tel
     */
    public function getTel()
    {
        return $this->tel;
    }

    /**
     * Set mail
     *
     * @param string $mail
     * @return $this
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
        return $this;
    }

    /**
     * Get mail
     *
     * @return string $mail
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Set webSite
     *
     * @param string $webSite
     * @return $this
     */
    public function setWebSite($webSite)
    {
        $this->webSite = $webSite;
        return $this;
    }

    /**
     * Get webSite
     *
     * @return string $webSite
     */
    public function getWebSite()
    {
        return $this->webSite;
    }

    /**
     * Set categories
     *
     * @param object $categories
     * @return $this
     */
    public function setCategories(\object $categories)
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * Get categories
     *
     * @return object $categories
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set openHours
     *
     * @param object $openHours
     * @return $this
     */
    public function setOpenHours(\object $openHours)
    {
        $this->openHours = $openHours;
        return $this;
    }

    /**
     * Get openHours
     *
     * @return object $openHours
     */
    public function getOpenHours()
    {
        return $this->openHours;
    }

    /**
     * Set openHoursMoreInfos
     *
     * @param string $openHoursMoreInfos
     * @return $this
     */
    public function setOpenHoursMoreInfos($openHoursMoreInfos)
    {
        $this->openHoursMoreInfos = $openHoursMoreInfos;
        return $this;
    }

    /**
     * Get openHoursMoreInfos
     *
     * @return string $openHoursMoreInfos
     */
    public function getOpenHoursMoreInfos()
    {
        return $this->openHoursMoreInfos;
    }

    /**
     * Set contributor
     *
     * @param string $contributor
     * @return $this
     */
    public function setContributor($contributor)
    {
        $this->contributor = $contributor;
        return $this;
    }

    /**
     * Get contributor
     *
     * @return string $contributor
     */
    public function getContributor()
    {
        return $this->contributor;
    }

    /**
     * Set contributorMail
     *
     * @param string $contributorMail
     * @return $this
     */
    public function setContributorMail($contributorMail)
    {
        $this->contributorMail = $contributorMail;
        return $this;
    }

    /**
     * Get contributorMail
     *
     * @return string $contributorMail
     */
    public function getContributorMail()
    {
        return $this->contributorMail;
    }

    /**
     * Set validationCode
     *
     * @param string $validationCode
     * @return $this
     */
    public function setValidationCode($validationCode)
    {
        $this->validationCode = $validationCode;
        return $this;
    }

    /**
     * Get validationCode
     *
     * @return string $validationCode
     */
    public function getValidationCode()
    {
        return $this->validationCode;
    }
}
