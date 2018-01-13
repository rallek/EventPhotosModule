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

namespace RK\EventPhotosModule\Helper;

use RK\EventPhotosModule\Helper\Base\AbstractCategoryHelper;

/**
 * Category helper implementation class.
 */
class CategoryHelper extends AbstractCategoryHelper
{
    // feel free to extend the category helper here
protected function requireAccessForAll($entity)
{
    return true;
}

}
