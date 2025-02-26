<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Model\Activity\ActivitySourceExpander;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivityApiController extends AbstractFOSRestController
{
    private const SERIALIZER_GROUP = 'api';

    /**
     * @var ActivitySourceExpander
     */
    private ActivitySourceExpander $activitySourceExpander;

    /**
     * @param ActivitySourceExpander $activitySourceExpander
     */
    public function __construct(ActivitySourceExpander $activitySourceExpander)
    {
        $this->activitySourceExpander = $activitySourceExpander;
    }
    
    /**
     * @Rest\Get("/api/activities", name="activities")
     */
    public function getActivities(Request $request): View
    {
        $request->setLocale($request->query->get('locale', 'en'));

        $activities = $this->getDoctrine()
            ->getRepository('App:Activity')
            ->findAllOrdered();

        $localizedActivities = [];
        foreach ($activities as $activity) {
            /** @var $activity Activity */
            if (!$activity->translate($request->getLocale(), false)->isEmpty()) {
                $this->activitySourceExpander->expandSource($activity);
                $localizedActivities[] = $activity;
            } else {
                break;
            }
        }

        return $this->view($localizedActivities, Response::HTTP_OK)->setContext((new Context())->addGroup(self::SERIALIZER_GROUP));
    }

    /**
     * @Rest\Get("/api/activity/{id}", name="activity")
     */
    public function getActivity(Request $request, string $id): View
    {
        $request->setLocale($request->query->get('locale', 'en'));

        /** @var $activity Activity */
        $activity = $this->getDoctrine()
            ->getRepository('App:Activity')
            ->find($id);

        $this->activitySourceExpander->expandSource($activity);

        return $this->view($activity, Response::HTTP_OK)->setContext((new Context())->addGroup(self::SERIALIZER_GROUP));
    }
}
