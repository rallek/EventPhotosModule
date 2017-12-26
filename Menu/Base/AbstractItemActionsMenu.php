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

namespace RK\EventPhotosModule\Menu\Base;

use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuItem;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\UsersModule\Constant as UsersConstant;
use RK\EventPhotosModule\Entity\AlbumEntity;
use RK\EventPhotosModule\Entity\AlbumItemEntity;

/**
 * This is the item actions menu implementation class.
 */
class AbstractItemActionsMenu implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use TranslatorTrait;

    /**
     * Sets the translator.
     *
     * @param TranslatorInterface $translator Translator service instance
     */
    public function setTranslator(/*TranslatorInterface */$translator)
    {
        $this->translator = $translator;
    }

    /**
     * Builds the menu.
     *
     * @param FactoryInterface $factory Menu factory
     * @param array            $options List of additional options
     *
     * @return MenuItem The assembled menu
     */
    public function menu(FactoryInterface $factory, array $options = [])
    {
        $menu = $factory->createItem('itemActions');
        if (!isset($options['entity']) || !isset($options['area']) || !isset($options['context'])) {
            return $menu;
        }

        $this->setTranslator($this->container->get('translator.default'));

        $entity = $options['entity'];
        $routeArea = $options['area'];
        $context = $options['context'];

        $permissionApi = $this->container->get('zikula_permissions_module.api.permission');
        $currentUserApi = $this->container->get('zikula_users_module.current_user');
        $entityDisplayHelper = $this->container->get('rk_eventphotos_module.entity_display_helper');
        $menu->setChildrenAttribute('class', 'list-inline');

        $currentUserId = $currentUserApi->isLoggedIn() ? $currentUserApi->get('uid') : UsersConstant::USER_ID_ANONYMOUS;
        if ($entity instanceof AlbumEntity) {
            $component = 'RKEventPhotosModule:Album:';
            $instance = $entity->getKey() . '::';
            $routePrefix = 'rkeventphotosmodule_album_';
            $isOwner = $currentUserId > 0 && null !== $entity->getCreatedBy() && $currentUserId == $entity->getCreatedBy()->getUid();
        
            if ($routeArea == 'admin') {
                $title = $this->__('Preview', 'rkeventphotosmodule');
                $menu->addChild($title, [
                    'route' => $routePrefix . 'display',
                    'routeParameters' => $entity->createUrlArgs()
                ]);
                $menu[$title]->setLinkAttribute('target', '_blank');
                $menu[$title]->setLinkAttribute('title', $this->__('Open preview page', 'rkeventphotosmodule'));
                if ($context == 'display') {
                    $menu[$title]->setLinkAttribute('class', 'btn btn-sm btn-default');
                }
                $menu[$title]->setAttribute('icon', 'fa fa-search-plus');
            }
            if ($context != 'display') {
                $title = $this->__('Details', 'rkeventphotosmodule');
                $menu->addChild($title, [
                    'route' => $routePrefix . $routeArea . 'display',
                    'routeParameters' => $entity->createUrlArgs()
                ]);
                $menu[$title]->setLinkAttribute('title', str_replace('"', '', $entityDisplayHelper->getFormattedTitle($entity)));
                if ($context == 'display') {
                    $menu[$title]->setLinkAttribute('class', 'btn btn-sm btn-default');
                }
                $menu[$title]->setAttribute('icon', 'fa fa-eye');
            }
            if ($permissionApi->hasPermission($component, $instance, ACCESS_EDIT)) {
                // only allow editing for the owner or people with higher permissions
                if ($isOwner || $permissionApi->hasPermission($component, $instance, ACCESS_ADD)) {
                    $title = $this->__('Edit', 'rkeventphotosmodule');
                    $menu->addChild($title, [
                        'route' => $routePrefix . $routeArea . 'edit',
                        'routeParameters' => $entity->createUrlArgs()
                    ]);
                    $menu[$title]->setLinkAttribute('title', $this->__('Edit this album', 'rkeventphotosmodule'));
                    if ($context == 'display') {
                        $menu[$title]->setLinkAttribute('class', 'btn btn-sm btn-default');
                    }
                    $menu[$title]->setAttribute('icon', 'fa fa-pencil-square-o');
                    $title = $this->__('Reuse', 'rkeventphotosmodule');
                    $menu->addChild($title, [
                        'route' => $routePrefix . $routeArea . 'edit',
                        'routeParameters' => ['astemplate' => $entity->getKey()]
                    ]);
                    $menu[$title]->setLinkAttribute('title', $this->__('Reuse for new album', 'rkeventphotosmodule'));
                    if ($context == 'display') {
                        $menu[$title]->setLinkAttribute('class', 'btn btn-sm btn-default');
                    }
                    $menu[$title]->setAttribute('icon', 'fa fa-files-o');
                }
            }
            if ($permissionApi->hasPermission($component, $instance, ACCESS_DELETE)) {
                $title = $this->__('Delete', 'rkeventphotosmodule');
                $menu->addChild($title, [
                    'route' => $routePrefix . $routeArea . 'delete',
                    'routeParameters' => $entity->createUrlArgs()
                ]);
                $menu[$title]->setLinkAttribute('title', $this->__('Delete this album', 'rkeventphotosmodule'));
                if ($context == 'display') {
                    $menu[$title]->setLinkAttribute('class', 'btn btn-sm btn-danger');
                }
                $menu[$title]->setAttribute('icon', 'fa fa-trash-o');
            }
            if ($context == 'display') {
                $title = $this->__('Back to overview', 'rkeventphotosmodule');
                $menu->addChild($title, [
                    'route' => $routePrefix . $routeArea . 'view'
                ]);
                $menu[$title]->setLinkAttribute('title', $title);
                if ($context == 'display') {
                    $menu[$title]->setLinkAttribute('class', 'btn btn-sm btn-default');
                }
                $menu[$title]->setAttribute('icon', 'fa fa-reply');
            }
            
            // more actions for adding new related items
            
            $relatedComponent = 'RKEventPhotosModule:AlbumItem:';
            $relatedInstance = $entity->getKey() . '::';
            if ($isOwner || $permissionApi->hasPermission($relatedComponent, $relatedInstance, ACCESS_ADD)) {
                $title = $this->__('Create album items', 'rkeventphotosmodule');
                $menu->addChild($title, [
                    'route' => 'rkeventphotosmodule_albumitem_' . $routeArea . 'edit',
                    'routeParameters' => ['album' => $entity->getKey()]
                ]);
                $menu[$title]->setLinkAttribute('title', $title);
                if ($context == 'display') {
                    $menu[$title]->setLinkAttribute('class', 'btn btn-sm btn-default');
                }
                $menu[$title]->setAttribute('icon', 'fa fa-plus');
            }
        }
        if ($entity instanceof AlbumItemEntity) {
            $component = 'RKEventPhotosModule:AlbumItem:';
            $instance = $entity->getKey() . '::';
            $routePrefix = 'rkeventphotosmodule_albumitem_';
            $isOwner = $currentUserId > 0 && null !== $entity->getCreatedBy() && $currentUserId == $entity->getCreatedBy()->getUid();
        
            if ($routeArea == 'admin') {
                $title = $this->__('Preview', 'rkeventphotosmodule');
                $menu->addChild($title, [
                    'route' => $routePrefix . 'display',
                    'routeParameters' => $entity->createUrlArgs()
                ]);
                $menu[$title]->setLinkAttribute('target', '_blank');
                $menu[$title]->setLinkAttribute('title', $this->__('Open preview page', 'rkeventphotosmodule'));
                if ($context == 'display') {
                    $menu[$title]->setLinkAttribute('class', 'btn btn-sm btn-default');
                }
                $menu[$title]->setAttribute('icon', 'fa fa-search-plus');
            }
            if ($context != 'display') {
                $title = $this->__('Details', 'rkeventphotosmodule');
                $menu->addChild($title, [
                    'route' => $routePrefix . $routeArea . 'display',
                    'routeParameters' => $entity->createUrlArgs()
                ]);
                $menu[$title]->setLinkAttribute('title', str_replace('"', '', $entityDisplayHelper->getFormattedTitle($entity)));
                if ($context == 'display') {
                    $menu[$title]->setLinkAttribute('class', 'btn btn-sm btn-default');
                }
                $menu[$title]->setAttribute('icon', 'fa fa-eye');
            }
            if ($permissionApi->hasPermission($component, $instance, ACCESS_EDIT)) {
                // only allow editing for the owner or people with higher permissions
                if ($isOwner || $permissionApi->hasPermission($component, $instance, ACCESS_ADD)) {
                    $title = $this->__('Edit', 'rkeventphotosmodule');
                    $menu->addChild($title, [
                        'route' => $routePrefix . $routeArea . 'edit',
                        'routeParameters' => $entity->createUrlArgs()
                    ]);
                    $menu[$title]->setLinkAttribute('title', $this->__('Edit this album item', 'rkeventphotosmodule'));
                    if ($context == 'display') {
                        $menu[$title]->setLinkAttribute('class', 'btn btn-sm btn-default');
                    }
                    $menu[$title]->setAttribute('icon', 'fa fa-pencil-square-o');
                    $title = $this->__('Reuse', 'rkeventphotosmodule');
                    $menu->addChild($title, [
                        'route' => $routePrefix . $routeArea . 'edit',
                        'routeParameters' => ['astemplate' => $entity->getKey()]
                    ]);
                    $menu[$title]->setLinkAttribute('title', $this->__('Reuse for new album item', 'rkeventphotosmodule'));
                    if ($context == 'display') {
                        $menu[$title]->setLinkAttribute('class', 'btn btn-sm btn-default');
                    }
                    $menu[$title]->setAttribute('icon', 'fa fa-files-o');
                }
            }
            if ($permissionApi->hasPermission($component, $instance, ACCESS_DELETE)) {
                $title = $this->__('Delete', 'rkeventphotosmodule');
                $menu->addChild($title, [
                    'route' => $routePrefix . $routeArea . 'delete',
                    'routeParameters' => $entity->createUrlArgs()
                ]);
                $menu[$title]->setLinkAttribute('title', $this->__('Delete this album item', 'rkeventphotosmodule'));
                if ($context == 'display') {
                    $menu[$title]->setLinkAttribute('class', 'btn btn-sm btn-danger');
                }
                $menu[$title]->setAttribute('icon', 'fa fa-trash-o');
            }
            if ($context == 'display') {
                $title = $this->__('Back to overview', 'rkeventphotosmodule');
                $menu->addChild($title, [
                    'route' => $routePrefix . $routeArea . 'view'
                ]);
                $menu[$title]->setLinkAttribute('title', $title);
                if ($context == 'display') {
                    $menu[$title]->setLinkAttribute('class', 'btn btn-sm btn-default');
                }
                $menu[$title]->setAttribute('icon', 'fa fa-reply');
            }
        }

        return $menu;
    }
}
