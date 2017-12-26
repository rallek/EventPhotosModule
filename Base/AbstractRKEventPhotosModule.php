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

namespace RK\EventPhotosModule\Base {

    use Zikula\Core\AbstractModule;
    
    /**
     * Module base class.
     */
    abstract class AbstractRKEventPhotosModule extends AbstractModule
    {
    }
}

namespace {

    if (!class_exists('Content_AbstractContentType')) {
        if (file_exists('modules/Content/lib/Content/AbstractContentType.php')) {
            require_once 'modules/Content/lib/Content/AbstractType.php';
            require_once 'modules/Content/lib/Content/AbstractContentType.php';
        } else {
            class Content_AbstractContentType {}
        }
    }

    class RKEventPhotosModule_ContentType_ItemList extends \RK\EventPhotosModule\ContentType\ItemList {
    }
    class RKEventPhotosModule_ContentType_Item extends \RK\EventPhotosModule\ContentType\Item {
    }
}
