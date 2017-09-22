<?php

namespace AppBundle\Service;

use GuzzleHttp\Client;

use AppBundle\Entity\NearEarthObject;

/**
 * NearEarthObjectService Service
 */
class NearEarthObjectService
{
	function transformObject(NearEarthObject $object)
	{
		return [
            'is_hazardous'  => (bool) $object->getIsHazardous(),
            'speed'         => $object->getSpeed(),
            'name'          => $object->getName(),
            'date'          => $object->getDate(),
            'reference'     => $object->getReference(),

        ];
	}

	function transformCollection(array $data)
	{
		return array_map([$this, 'transformObject'], $data);
	}
}
