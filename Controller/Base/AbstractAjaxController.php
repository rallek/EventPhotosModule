<?php
/**
 * EventPhotos.
 *
 * @copyright Ralf Koester (RK)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Ralf Koester <ralf@familie-koester.de>.
 * @link http://k62.de
 * @link http://zikula.org
 * @version Generated by ModuleStudio 1.3.0 (https://modulestudio.de).
 */

namespace RK\EventPhotosModule\Controller\Base;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;

/**
 * Ajax controller base class.
 */
abstract class AbstractAjaxController extends AbstractController
{
    
    /**
     * Retrieve item list for finder selections in Forms, Content type plugin and Scribite.
     *
     * @param string $ot      Name of currently used object type
     * @param string $sort    Sorting field
     * @param string $sortdir Sorting direction
     *
     * @return JsonResponse
     */
    public function getItemListFinderAction(Request $request)
    {
        if (!$this->hasPermission('RKEventPhotosModule::Ajax', '::', ACCESS_EDIT)) {
            return true;
        }
        
        $objectType = $request->query->getAlnum('ot', 'album');
        $controllerHelper = $this->get('rk_eventphotos_module.controller_helper');
        $contextArgs = ['controller' => 'ajax', 'action' => 'getItemListFinder'];
        if (!in_array($objectType, $controllerHelper->getObjectTypes('controllerAction', $contextArgs))) {
            $objectType = $controllerHelper->getDefaultObjectType('controllerAction', $contextArgs);
        }
        
        $repository = $this->get('rk_eventphotos_module.entity_factory')->getRepository($objectType);
        $entityDisplayHelper = $this->get('rk_eventphotos_module.entity_display_helper');
        $descriptionFieldName = $entityDisplayHelper->getDescriptionFieldName($objectType);
        
        $sort = $request->query->getAlnum('sort', '');
        if (empty($sort) || !in_array($sort, $repository->getAllowedSortingFields())) {
            $sort = $repository->getDefaultSortingField();
        }
        
        $sdir = strtolower($request->query->getAlpha('sortdir', ''));
        if ($sdir != 'asc' && $sdir != 'desc') {
            $sdir = 'asc';
        }
        
        $where = ''; // filters are processed inside the repository class
        $searchTerm = $request->query->get('q', '');
        $sortParam = $sort . ' ' . $sdir;
        
        $entities = [];
        if ($searchTerm != '') {
            list ($entities, $totalAmount) = $repository->selectSearch($searchTerm, [], $sortParam, 1, 50);
        } else {
            $entities = $repository->selectWhere($where, $sortParam);
        }
        
        $slimItems = [];
        $component = 'RKEventPhotosModule:' . ucfirst($objectType) . ':';
        foreach ($entities as $item) {
            $itemId = $item->getKey();
            if (!$this->hasPermission($component, $itemId . '::', ACCESS_READ)) {
                continue;
            }
            $slimItems[] = $this->prepareSlimItem($repository, $objectType, $item, $itemId, $descriptionFieldName);
        }
        
        // return response
        return new JsonResponse($slimItems);
    }
    
    /**
     * Builds and returns a slim data array from a given entity.
     *
     * @param EntityRepository $repository       Repository for the treated object type
     * @param string           $objectType       The currently treated object type
     * @param object           $item             The currently treated entity
     * @param string           $itemId           Data item identifier(s)
     * @param string           $descriptionField Name of item description field
     *
     * @return array The slim data representation
     */
    protected function prepareSlimItem($repository, $objectType, $item, $itemId, $descriptionField)
    {
        $previewParameters = [
            $objectType => $item
        ];
        $contextArgs = ['controller' => $objectType, 'action' => 'display'];
        $previewParameters = $this->get('rk_eventphotos_module.controller_helper')->addTemplateParameters($objectType, $previewParameters, 'controllerAction', $contextArgs);
    
        $previewInfo = base64_encode($this->get('twig')->render('@RKEventPhotosModule/External/' . ucfirst($objectType) . '/info.html.twig', $previewParameters));
    
        $title = $this->get('rk_eventphotos_module.entity_display_helper')->getFormattedTitle($item);
        $description = $descriptionField != '' ? $item[$descriptionField] : '';
    
        return [
            'id'          => $itemId,
            'title'       => str_replace('&amp;', '&', $title),
            'description' => $description,
            'previewInfo' => $previewInfo
        ];
    }
    
    /**
     * Searches for entities for auto completion usage.
     *
     * @param Request $request Current request instance
     *
     * @return JsonResponse
     */
    public function getItemListAutoCompletionAction(Request $request)
    {
        if (!$this->hasPermission('RKEventPhotosModule::Ajax', '::', ACCESS_EDIT)) {
            return true;
        }
        
        $objectType = $request->query->getAlnum('ot', 'album');
        $controllerHelper = $this->get('rk_eventphotos_module.controller_helper');
        $contextArgs = ['controller' => 'ajax', 'action' => 'getItemListAutoCompletion'];
        if (!in_array($objectType, $controllerHelper->getObjectTypes('controllerAction', $contextArgs))) {
            $objectType = $controllerHelper->getDefaultObjectType('controllerAction', $contextArgs);
        }
        
        $repository = $this->get('rk_eventphotos_module.entity_factory')->getRepository($objectType);
        $fragment = $request->query->get('fragment', '');
        $exclude = $request->query->get('exclude', '');
        $exclude = !empty($exclude) ? explode(',', str_replace(', ', ',', $exclude)) : [];
        
        // parameter for used sorting field
        $sort = $request->query->get('sort', '');
        if (empty($sort) || !in_array($sort, $repository->getAllowedSortingFields())) {
            $sort = $repository->getDefaultSortingField();
            $request->query->set('sort', $sort);
            // set default sorting in route parameters (e.g. for the pager)
            $routeParams = $request->attributes->get('_route_params');
            $routeParams['sort'] = $sort;
            $request->attributes->set('_route_params', $routeParams);
        }
        $sortParam = $sort . ' asc';
        
        $currentPage = 1;
        $resultsPerPage = 20;
        
        // get objects from database
        list($entities, $objectCount) = $repository->selectSearch($fragment, $exclude, $sortParam, $currentPage, $resultsPerPage);
        
        $resultItems = [];
        
        if ((is_array($entities) || is_object($entities)) && count($entities) > 0) {
            $entityDisplayHelper = $this->get('rk_eventphotos_module.entity_display_helper');
            $descriptionFieldName = $entityDisplayHelper->getDescriptionFieldName($objectType);
            $previewFieldName = $entityDisplayHelper->getPreviewFieldName($objectType);
            $imagineCacheManager = $this->get('liip_imagine.cache.manager');
            $imageHelper = $this->get('rk_eventphotos_module.image_helper');
            $thumbRuntimeOptions = $imageHelper->getRuntimeOptions($objectType, $previewFieldName, 'controllerAction', $contextArgs);
            foreach ($entities as $item) {
                $itemTitle = $entityDisplayHelper->getFormattedTitle($item);
                $itemTitleStripped = str_replace('"', '', $itemTitle);
                $itemDescription = isset($item[$descriptionFieldName]) && !empty($item[$descriptionFieldName]) ? $item[$descriptionFieldName] : '';//$this->__('No description yet.')
                if (!empty($itemDescription)) {
                    $itemDescription = substr($itemDescription, 0, 50) . '&hellip;';
                }
        
                $resultItem = [
                    'id' => $item->getKey(),
                    'title' => $itemTitle,
                    'description' => $itemDescription,
                    'image' => ''
                ];
        
                // check for preview image
                if (!empty($previewFieldName) && !empty($item[$previewFieldName])) {
                    $thumbImagePath = $imagineCacheManager->getThumb($item[$previewFieldName]->getPathname(), 'zkroot', $thumbRuntimeOptions);
                    $resultItem['image'] = '<img src="' . $thumbImagePath . '" width="50" height="50" alt="' . $itemTitleStripped . '" />';
                }
        
                $resultItems[] = $resultItem;
            }
        }
        
        return new JsonResponse($resultItems);
    }
    
    /**
     * Attachs a given hook assignment by creating the corresponding assignment data record.
     *
     * @param Request $request Current request instance
     *
     * @return JsonResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function attachHookObjectAction(Request $request)
    {
        if (!$this->hasPermission('RKEventPhotosModule::Ajax', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        
        $subscriberOwner = $request->request->get('owner', '');
        $subscriberAreaId = $request->request->get('areaId', '');
        $subscriberObjectId = $request->request->getInt('objectId', 0);
        $subscriberUrl = $request->request->get('url', '');
        $assignedEntity = $request->request->get('assignedEntity', '');
        $assignedId = $request->request->getInt('assignedId', 0);
        
        if (!$subscriberOwner || !$subscriberAreaId || !$subscriberObjectId || !$assignedEntity || !$assignedId) {
            return new JsonResponse($this->__('Error: invalid input.'), JsonResponse::HTTP_BAD_REQUEST);
        }
        
        $subscriberUrl = !empty($subscriberUrl) ? unserialize($subscriberUrl) : [];
        
        $assignment = new \RK\EventPhotosModule\Entity\HookAssignmentEntity();
        $assignment->setSubscriberOwner($subscriberOwner);
        $assignment->setSubscriberAreaId($subscriberAreaId);
        $assignment->setSubscriberObjectId($subscriberObjectId);
        $assignment->setSubscriberUrl($subscriberUrl);
        $assignment->setAssignedEntity($assignedEntity);
        $assignment->setAssignedId($assignedId);
        $assignment->setUpdatedDate(new \DateTime());
        
        $entityManager = $this->get('rk_eventphotos_module.entity_factory')->getObjectManager();
        $entityManager->persist($assignment);
        $entityManager->flush();
        
        // return response
        return new JsonResponse([
            'id' => $assignment->getId()
        ]);
    }
    
    /**
     * Detachs a given hook assignment by removing the corresponding assignment data record.
     *
     * @param Request $request Current request instance
     *
     * @return JsonResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function detachHookObjectAction(Request $request)
    {
        if (!$this->hasPermission('RKEventPhotosModule::Ajax', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        
        $id = $request->request->getInt('id', 0);
        if (!$id) {
            return new JsonResponse($this->__('Error: invalid input.'), JsonResponse::HTTP_BAD_REQUEST);
        }
        
        $entityFactory = $this->get('rk_eventphotos_module.entity_factory');
        $qb = $entityFactory->getObjectManager()->createQueryBuilder();
        $qb->delete('RK\EventPhotosModule\Entity\HookAssignmentEntity', 'tbl')
           ->where('tbl.id = :identifier')
           ->setParameter('identifier', $id);
        
        $query = $qb->getQuery();
        $query->execute();
        
        // return response
        return new JsonResponse([
            'id' => $id
        ]);
    }
}
