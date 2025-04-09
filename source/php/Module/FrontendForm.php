<?php

/** @noinspection PhpMissingFieldTypeInspection */

/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */

namespace ModularityFrontendForm\Module;

use ModularityFrontendForm\Module\FormStep;
use ComponentLibrary\Init as ComponentLibraryInit;
use Throwable;

use AcfService\Contracts\EnqueueUploader;
use AcfService\Contracts\Form;
use AcfService\Contracts\FormHead;
use AcfService\Contracts\GetFieldGroups;
use AcfService\Implementations\NativeAcfService;

use WpService\Contracts\WpEnqueueStyle;
use WpService\Implementations\NativeWpService;
use WpService\Contracts\__;
use WpService\Contracts\AddFilter;
use WpService\Contracts\AddAction;
use WpService\Contracts\IsUserLoggedIn;
use WpService\Contracts\GetQueryVar;
use WpService\Contracts\GetPostType;
use WpService\Contracts\GetPostTypeObject;
use WpService\Contracts\GetPermalink;
use WpService\Contracts\GetPostMeta;
use WpService\Contracts\UpdatePostMeta;
use WpService\Implementations\WpServiceWithTypecastedReturns;
use AcfService\AcfService;
use AcfService\Contracts\AcfGetFields;
use ModularityFrontendForm\Module\FormatSteps;

/**
 * @property string $description
 * @property string $namePlural
 * @property string $nameSingular
 * @property int $ID
 */
class FrontendForm extends \Modularity\Module
{
    public $slug     = 'frontend-form';
    public $supports = [];
    public $hidden   = false;
    public $cacheTtl = 0;

    private $formStepQueryParam  = 'step'; // The query parameter for the form steps.
    private $formIdQueryParam    = 'formid'; // The query parameter for the form id.
    private $formTokenQueryParam = 'token';  // The query parameter for the form token.

    private $formSecurity = null; // The form security service.

    private $blade = null;

    private WpEnqueueStyle&__&IsUserLoggedIn&AddFilter&AddAction&GetQueryVar&GetPostType&GetPostTypeObject&GetPermalink&GetPostMeta&UpdatePostMeta $wpService;
    private AcfService $acfService;

    private FormatSteps $formatSteps;

    public function init(): void
    {
        $this->wpService    = new WpServiceWithTypecastedReturns(new NativeWpService());
        $this->acfService   = new NativeAcfService();
        $this->formatSteps  = new FormatSteps($this->acfService);

        //Manages form security
        $this->formSecurity = new FormSecurity(
            $this->wpService,
            $this->formIdQueryParam,
            $this->formTokenQueryParam
        );

        //Form admin service
        $formAdmin = new FormAdmin($this->wpService, $this->acfService, 'formStepGroup');
        $formAdmin->addHooks();

        //Set module properties
        $this->nameSingular = $this->wpService->__('Frontend Form', 'modularity-frontend-form');
        $this->namePlural   = $this->wpService->__('Frontend Forms', 'modularity-frontend-form');
        $this->description  = $this->wpService->__('Module for creating forms.', 'modularity-frontend-form');

        //Add query vars that should be allowed in context.
        $this->wpService->addFilter('query_vars', [$this, 'registerFormQueryVars']);
    }

    /**
     * Retrieves the form data.
     *
     * This method retrieves the form data by checking if the form is empty, protected, or needs a tokenized request.
     *
     * @return array The form data.
     */
    public function data(): array
    {
        //Needs to be called, otherwise a notice will be thrown.
        $data   = [];
        $fields = (object) $this->getFields();

        $data['steps'] = $this->formatSteps->formatSteps($fields->formSteps ?? []);

        return $data;
    }

    /**
     * Retrives default values for keys used in the form display.
     * This prevents notices when the keys are not set.
     *
     * @return array The default keys and values.
     */
    private function defaultDataResponse(): array
    {
        return [
            'empty' => false,
            'error' => false
        ];
    }

    /**
     * Retrives varous text strings for the form.
     *
     * @return object The (translated) text strings.
     */
    private function getLang(): object
    {
        $disclaimer = $this->wpService->__(
            <<<EOD
            By submitting this form, you're agreeing to our terms and conditions. 
            You're also consenting to us processing your personal data in line with GDPR regulations, 
            and confirming that you have full rights to use all provided content.
            EOD, 'modularity-frontend-form'
        );

        return (object) [
            'disclaimer' => $disclaimer,
            'edit'       => $this->wpService->__('Edit', 'modularity-frontend-form'),
            'submit'     => $this->wpService->__('Submit', 'modularity-frontend-form'),
            'previous'   => $this->wpService->__('Previous', 'modularity-frontend-form'),
            'next'       => $this->wpService->__('Save and go to next step', 'modularity-frontend-form'),
            'of'         => $this->wpService->__('of', 'modularity-frontend-form'),
            'step'       => $this->wpService->__('Step', 'modularity-frontend-form'),
            'completed'  => $this->wpService->__('Completed', 'modularity-frontend-form')
        ];
    }

    public function template(): string
    {
        return 'frontend-form.blade.php';
    }

    /**
     * Enqueues the form scripts.
     *
     * This method enqueues the form scripts.
     *
     * @return void
     */
    public function script(): void
    {
    }

    /**
     * Enqueues the form styles.
     *
     * This method enqueues the form styles.
     *
     * @return void
     */
    public function style(): void
    {
    }

    /**
     * Registers multiple query variables for the form in order to be able to access them in get_query_var.
     *
     * This method takes an array of registered query variables and adds
     * the form step, form ID, and form token keys to it.
     *
     * @param array $registeredQueryVars The array of registered query variables.
     * @return array The updated array of registered query variables.
     */
    public function registerFormQueryVars(array $registeredQueryVars): array
    {
        return array_merge(
            $registeredQueryVars,
            [
                $this->formStepQueryParam,
                $this->formIdQueryParam,
                $this->formTokenQueryParam
            ]
        );
    }
}
