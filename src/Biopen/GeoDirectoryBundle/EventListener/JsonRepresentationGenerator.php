<?php
/**
 * @Author: Sebastian Castro
 * @Date:   2017-06-18 21:03:01
 * @Last Modified by:   Sebastian Castro
 * @Last Modified time: 2018-02-28 11:42:37
 */
namespace Biopen\GeoDirectoryBundle\EventListener;

use Biopen\GeoDirectoryBundle\Document\Element;
use Biopen\GeoDirectoryBundle\Document\Taxonomy;
use Biopen\GeoDirectoryBundle\Document\Category;
use Biopen\GeoDirectoryBundle\Document\Option;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use JMS\Serializer\SerializationContext;

class JsonRepresentationGenerator
{
	protected $serializer;

	/**
	* Constructor
	*/
	public function __construct($serializer)
	{
		 $this->serializer = $serializer;
	}

	public function postPersist(\Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $args)
	{
		$document = $args->getDocument();
		$dm = $args->getDocumentManager();

		if ($document instanceof Option || $document instanceof Category)
		{
			if (!$document->getIsFixture()) $this->updateTaxonomy($dm);
		}
		else if ($document instanceof Taxonomy)
		{
			$this->updateTaxonomy($dm);
		}
	}

	public function postUpdate(\Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $args)
	{
		$document = $args->getDocument();
		$dm = $args->getDocumentManager();

		if ($document instanceof Option || $document instanceof Category)
		{
			$this->updateTaxonomy($dm);
		}
		else if ($document instanceof Taxonomy)
		{
			$this->updateTaxonomy($dm);
		}
	}

	public function postRemove(\Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $args)
	{
		$document = $args->getDocument();
		$dm = $args->getDocumentManager();

		if ($document instanceof Option || $document instanceof Category)
		{
			  $this->updateTaxonomy($dm);
		}
	}

	private function updateTaxonomy($dm)
	{
		$taxonomy = $dm->getRepository('BiopenGeoDirectoryBundle:Taxonomy')->findAll()[0]; 
		$dm->refresh($taxonomy);		

		if ($taxonomy->getMainCategory())
		{
			$mainCategoryJson = $this->serializer->serialize($taxonomy->getMainCategory(), 'json');
			$taxonomy->setMainCategoryJson($mainCategoryJson);
		}

		if ($taxonomy->getOpenHoursCategory())
		{
			$mainCategoryJson = $this->serializer->serialize($taxonomy->getOpenHoursCategory(), 'json');
			$taxonomy->setOpenHoursCategoryJson($mainCategoryJson);
		}
		
		$options = $dm->getRepository('BiopenGeoDirectoryBundle:Option')->findAll();
		$optionsSerialized = [];

		foreach ($options as $key => $option) 
		{
			$optionsSerialized[] = $this->serializer->serialize($option, 'json', SerializationContext::create()->setGroups(['semantic']));
		}

		$optionsJson = '[' . implode(", ", $optionsSerialized) . ']';

		$taxonomy->setOptionsJson($optionsJson);	

		$dm->persist($taxonomy);
		$dm->flush();
	}
}