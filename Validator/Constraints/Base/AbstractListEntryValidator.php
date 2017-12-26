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

namespace RK\EventPhotosModule\Validator\Constraints\Base;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use RK\EventPhotosModule\Helper\ListEntriesHelper;

/**
 * List entry validator.
 */
abstract class AbstractListEntryValidator extends ConstraintValidator
{
    use TranslatorTrait;

    /**
     * @var ListEntriesHelper
     */
    protected $listEntriesHelper;

    /**
     * ListEntryValidator constructor.
     *
     * @param TranslatorInterface $translator        Translator service instance
     * @param ListEntriesHelper   $listEntriesHelper ListEntriesHelper service instance
     */
    public function __construct(TranslatorInterface $translator, ListEntriesHelper $listEntriesHelper)
    {
        $this->setTranslator($translator);
        $this->listEntriesHelper = $listEntriesHelper;
    }

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
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if ($constraint->propertyName == 'workflowState' && in_array($value, ['initial', 'deleted'])) {
            return;
    	}

        $listEntries = $this->listEntriesHelper->getEntries($constraint->entityName, $constraint->propertyName);
        $allowedValues = [];
        foreach ($listEntries as $entry) {
            $allowedValues[] = $entry['value'];
        }

        if (!$constraint->multiple) {
            // single-valued list
            if (!in_array($value, $allowedValues)) {
                $this->context->buildViolation(
                    $this->__f('The value "%value%" is not allowed for the "%property%" property.', [
                        '%value%' => $value,
                        '%property%' => $constraint->propertyName
                    ])
                )->addViolation();
            }

            return;
        }

        // multi-values list
        $selected = explode('###', $value);
        foreach ($selected as $singleValue) {
            if ($singleValue == '') {
                continue;
            }
            if (!in_array($singleValue, $allowedValues)) {
                $this->context->buildViolation(
                    $this->__f('The value "%value%" is not allowed for the "%property%" property.', [
                        '%value%' => $singleValue,
                        '%property%' => $constraint->propertyName
                    ])
                )->addViolation();
            }
        }

        $count = count($value);

        if (null !== $constraint->min && $count < $constraint->min) {
            $this->context->buildViolation(
                $this->__fn('You must select at least "%limit%" choice.', 'You must select at least "%limit%" choices.', $count, [
                    '%limit%' => $constraint->min
                ])
            )->addViolation();
        }
        if (null !== $constraint->max && $count > $constraint->max) {
            $this->context->buildViolation(
                $this->__fn('You must select at most "%limit%" choice.', 'You must select at most "%limit%" choices.', $count, [
                    '%limit%' => $constraint->max
                ])
            )->addViolation();
        }
    }
}
