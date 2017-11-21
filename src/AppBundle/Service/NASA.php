<?php

namespace AppBundle\Service;

use GuzzleHttp\Client;

use AppBundle\Entity\NearEarthObject;

/**
 * NASA Service
 */
class NASA
{
	// Call the NASA api to fetch neo based on following params
	// Start Date - Current date - 3 days
	// End Date - Today
	public function getNearEarthObjects()
	{
		$apiUrl = 'https://api.nasa.gov/neo/rest/v1/feed';
		$apiKey = 'API_KEY';

		// Start Date
		$startDate = date('Y-m-d', strtotime("-3 day"));

		// Current Date (Today)
		$endDate   = date('Y-m-d');

		$apiParams = [
			'api_key' 	 => $apiKey,
			'start_date' => $startDate,
			'end_date' 	 => $endDate,
		];

		$client = new Client();
		$response = $client->request('GET', $apiUrl, ['query' => $apiParams]);
		$data = $response->getBody()->getContents();
        $data = $this->transformNearEarthObjectData($data);
		return $data;
	}

	// Transform the data from NASA api, 
	// by ignoring the unwanted data 
	private function transformNearEarthObjectData($data) 
	{
    	$transformedData = [];
        $data = json_decode($data, 1);
        if($data['element_count'] == 0 || count($data['near_earth_objects']) == 0) return $transformedData;

		foreach ($data['near_earth_objects'] as $date => $items) 
		{
			foreach ($items as $object) 
			{
				array_push($transformedData, [
					'date'			=> $date,
					'reference' 	=> $object['neo_reference_id'],
					'is_hazardous' 	=> $object['is_potentially_hazardous_asteroid'],
					'speed'			=> $object['close_approach_data'][0]['relative_velocity']['kilometers_per_hour'],
					'name'			=> $object['name']
				]);
			}
        }
        return $transformedData;
	}

	// Insert the transformed data from NASA into DB
	public function insertNearEarthObjects($em, array $data)
	{
		// Get the references from data so that we will filters those that are already in DB
        $references = array_map(function($item){
            return $item['reference'];
        }, $data);

        // Get the data already in DB 
        $query = $em->getRepository('AppBundle:NearEarthObject')->createQueryBuilder('q');
        $existingData = $query->select('q.reference')->where($query->expr()->in('q.reference', $references))->getQuery()->getResult();
        $existingData = array_map(function($item){
        	return $item['reference'];
        }, $existingData);

        try{
	        // Insert data
	        $insertCounter = 0;
	        foreach ($data as $item) 
	        {
	        	if(in_array($item['reference'], $existingData)) continue;

	        	$nearEarthObject = new NearEarthObject();
		        $nearEarthObject->setReference($item['reference'])
		        				->setSpeed($item['speed'])
		        				->setDate($item['date'])
		        				->setName($item['name'])
		        				->setIsHazardous($item['is_hazardous']);

		        $em->persist($nearEarthObject);
		        $insertCounter++;
	        }
	        $em->flush();
        } catch(\Exception $e) {
        	return ['message' => $e->getMessage(), 'error' => true];
        }

        return ['count' => $insertCounter, 'success' => true];
	}
}
