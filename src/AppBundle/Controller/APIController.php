<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\NearEarthObject;
use AppBundle\Service\NearEarthObjectService;

class APIController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        $data = [
            'hello' => 'world!'
        ];

        return $this->json($data);
    }

    /**
     * @Route("/neo/hazardous", name="hazardous")
     */
    public function hazardous(NearEarthObjectService $service)
    {
        $objects = $this->getDoctrine()
                        ->getRepository(NearEarthObject::class)
                        ->findBy([
                            'isHazardous' => 1
                        ]);

        $objects = $service->transformCollection($objects);

        return $this->json($objects);
    }

    /**
     * @Route("/neo/fastest", name="fastest")
     */
    public function fastest(Request $request, NearEarthObjectService $service)
    {
        $hazardous = $this->getHazardousFromInput($request);

        // Query the fastest object
        $fastestObject = $this->getDoctrine()->getRepository(NearEarthObject::class)
                                ->findOneBy(
                                    ['isHazardous' => $hazardous],
                                    ['speed' => 'DESC']
                                );

        $fastestObject = $service->transformObject($fastestObject);

        return $this->json($fastestObject);
    }

    /**
     * @Route("/neo/best-year", name="bestYear")
     */
    public function bestYear(Request $request)
    {
        $hazardous = $this->getHazardousFromInput($request);
        $result = $this->getDoctrine()->getManager()->getRepository("AppBundle:NearEarthObject")->getObjectsByYear($hazardous); 

        return $this->json($result);
    }

    /**
     * @Route("/neo/best-month", name="bestMonth")
     */
    public function bestMonth(Request $request)
    {
        $hazardous = $this->getHazardousFromInput($request);
        $result = $this->getDoctrine()->getManager()->getRepository("AppBundle:NearEarthObject")->getObjectsByMonth($hazardous);

        // Convert month number to string 
        $date  = \DateTime::createFromFormat('!m', $result['month']);
        $month = $date->format('F');

        $result['month'] = $month;
        return $this->json($result);
    }

    private function getHazardousFromInput(Request $request)
    {
        // Default hazardous is false
        $hazardous = 0;

        // If hazardous is set from request and is true
        if($request->query->get('hazardous') and in_array($request->query->get('hazardous'), [1, 'true'])) $hazardous = 1;

        return $hazardous;
    }
}
