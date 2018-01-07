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

namespace RK\EventPhotosModule\Base;

use Doctrine\DBAL\Connection;
use RuntimeException;
use Zikula\Core\AbstractExtensionInstaller;
use Zikula\CategoriesModule\Entity\CategoryRegistryEntity;

/**
 * Installer base class.
 */
abstract class AbstractEventPhotosModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * Install the RKEventPhotosModule application.
     *
     * @return boolean True on success, or false
     *
     * @throws RuntimeException Thrown if database tables can not be created or another error occurs
     */
    public function install()
    {
        $logger = $this->container->get('logger');
        $userName = $this->container->get('zikula_users_module.current_user')->get('uname');
    
        // Check if upload directories exist and if needed create them
        try {
            $container = $this->container;
            $uploadHelper = new \RK\EventPhotosModule\Helper\UploadHelper(
                $container->get('translator.default'),
                $container->get('filesystem'),
                $container->get('session'),
                $container->get('logger'),
                $container->get('zikula_users_module.current_user'),
                $container->get('zikula_extensions_module.api.variable'),
                $container->getParameter('datadir')
            );
            $uploadHelper->checkAndCreateAllUploadFolders();
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
            $logger->error('{app}: User {user} could not create upload folders during installation. Error details: {errorMessage}.', ['app' => 'RKEventPhotosModule', 'user' => $userName, 'errorMessage' => $exception->getMessage()]);
        
            return false;
        }
        // create all tables from according entity definitions
        try {
            $this->schemaTool->create($this->listEntityClasses());
        } catch (\Exception $exception) {
            $this->addFlash('error', $this->__('Doctrine Exception') . ': ' . $exception->getMessage());
            $logger->error('{app}: Could not create the database tables during installation. Error details: {errorMessage}.', ['app' => 'RKEventPhotosModule', 'errorMessage' => $exception->getMessage()]);
    
            return false;
        }
    
        // set up all our vars with initial values
        $this->setVar('rowHeight', 190);
        $this->setVar('albumEntriesPerPage', 10);
        $this->setVar('linkOwnAlbumsOnAccountPage', true);
        $this->setVar('albumItemEntriesPerPage', 10);
        $this->setVar('linkOwnAlbumItemsOnAccountPage', true);
        $this->setVar('enableShrinkingForAlbumItemImage', false);
        $this->setVar('shrinkWidthAlbumItemImage', 800);
        $this->setVar('shrinkHeightAlbumItemImage', 600);
        $this->setVar('thumbnailModeAlbumItemImage', 'inset');
        $this->setVar('thumbnailWidthAlbumItemImageView', 32);
        $this->setVar('thumbnailHeightAlbumItemImageView', 24);
        $this->setVar('thumbnailWidthAlbumItemImageDisplay', 240);
        $this->setVar('thumbnailHeightAlbumItemImageDisplay', 180);
        $this->setVar('thumbnailWidthAlbumItemImageEdit', 240);
        $this->setVar('thumbnailHeightAlbumItemImageEdit', 180);
        $this->setVar('enabledFinderTypes', 'album###albumItem');
    
        $categoryRegistryIdsPerEntity = [];
    
        // add default entry for category registry (property named Main)
        $categoryHelper = new \RK\EventPhotosModule\Helper\CategoryHelper(
            $this->container->get('translator.default'),
            $this->container->get('request_stack'),
            $logger,
            $this->container->get('zikula_users_module.current_user'),
            $this->container->get('zikula_categories_module.category_registry_repository'),
            $this->container->get('zikula_categories_module.api.category_permission')
        );
        $categoryGlobal = $this->container->get('zikula_categories_module.category_repository')->findOneBy(['name' => 'Global']);
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
    
        $registry = new CategoryRegistryEntity();
        $registry->setModname('RKEventPhotosModule');
        $registry->setEntityname('AlbumEntity');
        $registry->setProperty($categoryHelper->getPrimaryProperty('Album'));
        $registry->setCategory($categoryGlobal);
    
        try {
            $entityManager->persist($registry);
            $entityManager->flush();
        } catch (\Exception $exception) {
            $this->addFlash('error', $this->__f('Error! Could not create a category registry for the %entity% entity.', ['%entity%' => 'album']));
            $logger->error('{app}: User {user} could not create a category registry for {entities} during installation. Error details: {errorMessage}.', ['app' => 'RKEventPhotosModule', 'user' => $userName, 'entities' => 'albums', 'errorMessage' => $exception->getMessage()]);
        }
        $categoryRegistryIdsPerEntity['album'] = $registry->getId();
    
        $registry = new CategoryRegistryEntity();
        $registry->setModname('RKEventPhotosModule');
        $registry->setEntityname('AlbumItemEntity');
        $registry->setProperty($categoryHelper->getPrimaryProperty('AlbumItem'));
        $registry->setCategory($categoryGlobal);
    
        try {
            $entityManager->persist($registry);
            $entityManager->flush();
        } catch (\Exception $exception) {
            $this->addFlash('error', $this->__f('Error! Could not create a category registry for the %entity% entity.', ['%entity%' => 'album item']));
            $logger->error('{app}: User {user} could not create a category registry for {entities} during installation. Error details: {errorMessage}.', ['app' => 'RKEventPhotosModule', 'user' => $userName, 'entities' => 'album items', 'errorMessage' => $exception->getMessage()]);
        }
        $categoryRegistryIdsPerEntity['albumItem'] = $registry->getId();
    
        // initialisation successful
        return true;
    }
    
    /**
     * Upgrade the RKEventPhotosModule application from an older version.
     *
     * If the upgrade fails at some point, it returns the last upgraded version.
     *
     * @param integer $oldVersion Version to upgrade from
     *
     * @return boolean True on success, false otherwise
     *
     * @throws RuntimeException Thrown if database tables can not be updated
     */
    public function upgrade($oldVersion)
    {
    /*
        $logger = $this->container->get('logger');
    
        // Upgrade dependent on old version number
        switch ($oldVersion) {
            case '1.0.0':
                // do something
                // ...
                // update the database schema
                try {
                    $this->schemaTool->update($this->listEntityClasses());
                } catch (\Exception $exception) {
                    $this->addFlash('error', $this->__('Doctrine Exception') . ': ' . $exception->getMessage());
                    $logger->error('{app}: Could not update the database tables during the upgrade. Error details: {errorMessage}.', ['app' => 'RKEventPhotosModule', 'errorMessage' => $exception->getMessage()]);
    
                    return false;
                }
        }
    */
    
        // update successful
        return true;
    }
    
    /**
     * Uninstall RKEventPhotosModule.
     *
     * @return boolean True on success, false otherwise
     *
     * @throws RuntimeException Thrown if database tables or stored workflows can not be removed
     */
    public function uninstall()
    {
        $logger = $this->container->get('logger');
    
        try {
            $this->schemaTool->drop($this->listEntityClasses());
        } catch (\Exception $exception) {
            $this->addFlash('error', $this->__('Doctrine Exception') . ': ' . $exception->getMessage());
            $logger->error('{app}: Could not remove the database tables during uninstallation. Error details: {errorMessage}.', ['app' => 'RKEventPhotosModule', 'errorMessage' => $exception->getMessage()]);
    
            return false;
        }
    
        // remove all module vars
        $this->delVars();
    
        // remove category registry entries
        $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
        $registries = $this->container->get('zikula_categories_module.category_registry_repository')->findBy(['modname' => 'RKEventPhotosModule']);
        foreach ($registries as $registry) {
            $entityManager->remove($registry);
        }
        $entityManager->flush();
    
        // remind user about upload folders not being deleted
        $uploadPath = $this->container->getParameter('datadir') . '/RKEventPhotosModule/';
        $this->addFlash('status', $this->__f('The upload directories at "%path%" can be removed manually.', ['%path%' => $uploadPath]));
    
        // uninstallation successful
        return true;
    }
    
    /**
     * Build array with all entity classes for RKEventPhotosModule.
     *
     * @return string[] List of class names
     */
    protected function listEntityClasses()
    {
        $classNames = [];
        $classNames[] = 'RK\EventPhotosModule\Entity\AlbumEntity';
        $classNames[] = 'RK\EventPhotosModule\Entity\AlbumTranslationEntity';
        $classNames[] = 'RK\EventPhotosModule\Entity\AlbumCategoryEntity';
        $classNames[] = 'RK\EventPhotosModule\Entity\AlbumItemEntity';
        $classNames[] = 'RK\EventPhotosModule\Entity\AlbumItemTranslationEntity';
        $classNames[] = 'RK\EventPhotosModule\Entity\AlbumItemCategoryEntity';
        $classNames[] = 'RK\EventPhotosModule\Entity\HookAssignmentEntity';
    
        return $classNames;
    }
}
