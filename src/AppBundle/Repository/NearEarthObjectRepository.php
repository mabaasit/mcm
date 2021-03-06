<?php

namespace AppBundle\Repository;

/**
 * NearEarthObjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class NearEarthObjectRepository extends \Doctrine\ORM\EntityRepository
{
    // Will return the data based on the best year.
    public function getObjectsByYear($hazardous)
    {
        $query = '
            SELECT SUBSTRING(neo.date, 1, 4) AS year, COUNT(*) AS total
            FROM near_earth_objects neo
            WHERE neo.is_hazardous = :hazardous
            GROUP BY year
            ORDER BY COUNT(*) DESC, year DESC LIMIT 1
            ';
        return $this->getDataByQuery($query, ['hazardous' => $hazardous]);
    }

    // Will return the data based on the best month (irrespective of year).
    public function getObjectsByMonth($hazardous)
    {
        $query = '
            SELECT SUBSTRING(neo.date, 6, 2) AS month, COUNT(*) AS total
            FROM near_earth_objects neo
            WHERE neo.is_hazardous = :hazardous
            GROUP BY month
            ORDER BY COUNT(*) DESC, month DESC LIMIT 1
            ';
        return $this->getDataByQuery($query, ['hazardous' => $hazardous]);
    }

    // Will execute the query based on the passed parameters 
    private function getDataByQuery($query, $params = [])
    {
    	$conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch();	
    }
}
