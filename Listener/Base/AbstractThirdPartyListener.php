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

namespace RK\EventPhotosModule\Listener\Base;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\ScribiteModule\Event\EditorHelperEvent;

/**
 * Event handler implementation class for special purposes and 3rd party api support.
 */
abstract class AbstractThirdPartyListener implements EventSubscriberInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;
    
    /**
     * @var Request
     */
    protected $request;
    
    /**
     * ThirdPartyListener constructor.
     *
     * @param Filesystem   $filesystem   Filesystem service instance
     * @param RequestStack $requestStack RequestStack service instance
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem, RequestStack $requestStack)
    {
        $this->filesystem = $filesystem;
        $this->request = $requestStack->getCurrentRequest();
    }
    
    /**
     * Makes our handlers known to the event system.
     */
    public static function getSubscribedEvents()
    {
        return [
            'module.content.gettypes'                 => ['contentGetTypes', 5],
            'module.scribite.editorhelpers'           => ['getEditorHelpers', 5],
            'moduleplugin.ckeditor.externalplugins'   => ['getCKEditorPlugins', 5],
            'moduleplugin.quill.externalplugins'      => ['getQuillPlugins', 5],
            'moduleplugin.summernote.externalplugins' => ['getSummernotePlugins', 5],
            'moduleplugin.tinymce.externalplugins'    => ['getTinyMcePlugins', 5]
        ];
    }
    
    
    /**
     * Listener for the `module.content.gettypes` event.
     *
     * This event occurs when the Content module is 'searching' for Content plugins.
     * The subject is an instance of Content_Types.
     * You can register custom content types as well as custom layout types.
     *
     * You can access general data available in the event.
     *
     * The event name:
     *     `echo 'Event: ' . $event->getName();`
     *
     * The current request's type: `MASTER_REQUEST` or `SUB_REQUEST`.
     * If a listener should only be active for the master request,
     * be sure to check that at the beginning of your method.
     *     `if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
     *         return;
     *     }`
     *
     * The kernel instance handling the current request:
     *     `$kernel = $event->getKernel();`
     *
     * The currently handled request:
     *     `$request = $event->getRequest();`
     *
     * @param \Zikula_Event $event The event instance
     */
    public function contentGetTypes(\Zikula_Event $event)
    {
        // intended is using the add() method to add a plugin like below
        $types = $event->getSubject();
        
        
        // plugin for showing a single item
        $types->add('RKEventPhotosModule_ContentType_Item');
        
        // plugin for showing a list of multiple items
        $types->add('RKEventPhotosModule_ContentType_ItemList');
    }
    
    /**
     * Listener for the `module.scribite.editorhelpers` event.
     *
     * This occurs when Scribite adds pagevars to the editor page.
     * RKEventPhotosModule will use this to add a javascript helper to add custom items.
     *
     * You can access general data available in the event.
     *
     * The event name:
     *     `echo 'Event: ' . $event->getName();`
     *
     * The current request's type: `MASTER_REQUEST` or `SUB_REQUEST`.
     * If a listener should only be active for the master request,
     * be sure to check that at the beginning of your method.
     *     `if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
     *         return;
     *     }`
     *
     * The kernel instance handling the current request:
     *     `$kernel = $event->getKernel();`
     *
     * The currently handled request:
     *     `$request = $event->getRequest();`
     *
     * @param EditorHelperEvent $event The event instance
     */
    public function getEditorHelpers(EditorHelperEvent $event)
    {
        // install assets for Scribite plugins
        $targetDir = 'web/modules/rkeventphotos';
        $finder = new Finder();
        if (!$this->filesystem->exists($targetDir)) {
            $this->filesystem->mkdir($targetDir, 0777);
            if (is_dir($originDir = 'modules/RK/EventPhotosModule/Resources/public')) {
                $this->filesystem->mirror($originDir, $targetDir, Finder::create()->in($originDir));
            }
            if (is_dir($originDir = 'modules/RK/EventPhotosModule/Resources/scribite')) {
                $targetDir .= '/scribite';
                $this->filesystem->mkdir($targetDir, 0777);
                $this->filesystem->mirror($originDir, $targetDir, Finder::create()->in($originDir));
            }
        }
    
        $event->getHelperCollection()->add(
            [
                'module' => 'RKEventPhotosModule',
                'type' => 'javascript',
                'path' => $this->request->getBasePath() . '/web/modules/rkeventphotos/js/RKEventPhotosModule.Finder.js'
            ]
        );
    }
    
    /**
     * Listener for the `moduleplugin.ckeditor.externalplugins` event.
     *
     * Adds external plugin to CKEditor.
     *
     * You can access general data available in the event.
     *
     * The event name:
     *     `echo 'Event: ' . $event->getName();`
     *
     * The current request's type: `MASTER_REQUEST` or `SUB_REQUEST`.
     * If a listener should only be active for the master request,
     * be sure to check that at the beginning of your method.
     *     `if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
     *         return;
     *     }`
     *
     * The kernel instance handling the current request:
     *     `$kernel = $event->getKernel();`
     *
     * The currently handled request:
     *     `$request = $event->getRequest();`
     *
     * @param GenericEvent $event The event instance
     */
    public function getCKEditorPlugins(GenericEvent $event)
    {
        $event->getSubject()->add([
            'name' => 'rkeventphotosmodule',
            'path' => $this->request->getBasePath() . '/web/modules/rkeventphotos/scribite/CKEditor/rkeventphotosmodule/',
            'file' => 'plugin.js',
            'img' => 'ed_rkeventphotosmodule.gif'
        ]);
    }
    
    /**
     * Listener for the `moduleplugin.quill.externalplugins` event.
     *
     * Adds external plugin to Quill.
     *
     * You can access general data available in the event.
     *
     * The event name:
     *     `echo 'Event: ' . $event->getName();`
     *
     * The current request's type: `MASTER_REQUEST` or `SUB_REQUEST`.
     * If a listener should only be active for the master request,
     * be sure to check that at the beginning of your method.
     *     `if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
     *         return;
     *     }`
     *
     * The kernel instance handling the current request:
     *     `$kernel = $event->getKernel();`
     *
     * The currently handled request:
     *     `$request = $event->getRequest();`
     *
     * @param GenericEvent $event The event instance
     */
    public function getQuillPlugins(GenericEvent $event)
    {
        $event->getSubject()->add([
            'name' => 'rkeventphotosmodule',
            'path' => $this->request->getBasePath() . '/web/modules/rkeventphotos/scribite/Quill/rkeventphotosmodule/plugin.js'
        ]);
    }
    
    /**
     * Listener for the `moduleplugin.summernote.externalplugins` event.
     *
     * Adds external plugin to Summernote.
     *
     * You can access general data available in the event.
     *
     * The event name:
     *     `echo 'Event: ' . $event->getName();`
     *
     * The current request's type: `MASTER_REQUEST` or `SUB_REQUEST`.
     * If a listener should only be active for the master request,
     * be sure to check that at the beginning of your method.
     *     `if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
     *         return;
     *     }`
     *
     * The kernel instance handling the current request:
     *     `$kernel = $event->getKernel();`
     *
     * The currently handled request:
     *     `$request = $event->getRequest();`
     *
     * @param GenericEvent $event The event instance
     */
    public function getSummernotePlugins(GenericEvent $event)
    {
        $event->getSubject()->add([
            'name' => 'rkeventphotosmodule',
            'path' => $this->request->getBasePath() . '/web/modules/rkeventphotos/scribite/Summernote/rkeventphotosmodule/plugin.js'
        ]);
    }
    
    /**
     * Listener for the `moduleplugin.tinymce.externalplugins` event.
     *
     * Adds external plugin to TinyMce.
     *
     * You can access general data available in the event.
     *
     * The event name:
     *     `echo 'Event: ' . $event->getName();`
     *
     * The current request's type: `MASTER_REQUEST` or `SUB_REQUEST`.
     * If a listener should only be active for the master request,
     * be sure to check that at the beginning of your method.
     *     `if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
     *         return;
     *     }`
     *
     * The kernel instance handling the current request:
     *     `$kernel = $event->getKernel();`
     *
     * The currently handled request:
     *     `$request = $event->getRequest();`
     *
     * @param GenericEvent $event The event instance
     */
    public function getTinyMcePlugins(GenericEvent $event)
    {
        $event->getSubject()->add([
            'name' => 'rkeventphotosmodule',
            'path' => $this->request->getBasePath() . '/web/modules/rkeventphotos/scribite/TinyMce/rkeventphotosmodule/plugin.js'
        ]);
    }
}
