<?php

/** @noinspection PhpMissingFieldTypeInspection */

/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpUndefinedNamespaceInspection */

namespace ModularityFrontendForm\Module;

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
use WpService\Contracts\WpRegisterScript;
use WpService\Contracts\WpEnqueueScript;
use WpService\Contracts\WpRegisterStyle;
use WpService\Contracts\GetPermalink;
use WpService\Contracts\GetPostMeta;
use WpService\Contracts\UpdatePostMeta;
use WpService\Implementations\WpServiceWithTypecastedReturns;
use AcfService\AcfService;
use ModularityFrontendForm\Module\FormatSteps;
use ModularityFrontendForm\Config\Config;

use ModularityFrontendForm\Helper\CacheBust;
use WpService\Contracts\GetRestUrl;
use WpService\Contracts\WpLocalizeScript;

/**
 * @property string $description
 * @property string $namePlural
 * @property string $nameSingular
 * @property int $ID
 */
class FrontendForm extends \Modularity\Module
{
    public $slug     = 'frontend-form'; //Note: Update config if 
    public $supports = [];
    public $hidden   = false;
    public $cacheTtl = 0;
    public CacheBust $cacheBust;

    private $formStepQueryParam  = 'step'; // The query parameter for the form steps.
    private $formIdQueryParam    = 'formid'; // The query parameter for the form id.
    private $formTokenQueryParam = 'token';  // The query parameter for the form token.

    private WpEnqueueStyle&__&IsUserLoggedIn&AddFilter&AddAction&GetQueryVar&GetPostType&GetPostTypeObject&GetPermalink&GetPostMeta&UpdatePostMeta&WpLocalizeScript&GetRestUrl&WpRegisterScript&WpRegisterStyle&WpEnqueueScript $wpService;
    private AcfService $acfService;

    private FormatSteps $formatSteps;

    public function init(): void
    {
        $this->wpService    = new WpServiceWithTypecastedReturns(new NativeWpService());
        $this->acfService   = new NativeAcfService();
        $this->formatSteps  = new FormatSteps(
            $this->wpService, 
            $this->acfService,
            new Config($this->wpService, 'modularity-frontend-form'), //TODO: Use a config factory,
            $this->getLang()
        );

        $this->cacheBust    = new CacheBust();

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

        //The module id
        $data['moduleId'] = $this->ID;

        //Steps
        $data['steps'] = $this->formatSteps->formatSteps($fields->formSteps ?? []);
        $data['stepsCount'] = count($data['steps']);

        //Language
        $data['lang'] = $this->getLang();

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
            'disclaimer'                => $disclaimer,
            'edit'                      => $this->wpService->__('Edit', 'modularity-frontend-form'),
            'submit'                    => $this->wpService->__('Submit', 'modularity-frontend-form'),
            'previous'                  => $this->wpService->__('Previous', 'modularity-frontend-form'),
            'next'                      => $this->wpService->__('Next', 'modularity-frontend-form'),
            'of'                        => $this->wpService->__('of', 'modularity-frontend-form'),
            'step'                      => $this->wpService->__('Step', 'modularity-frontend-form'),
            'completed'                 => $this->wpService->__('Completed', 'modularity-frontend-form'),
            'noResultsFound'            => $this->wpService->__('No results found', 'modularity-frontend-form'),
            'searchPlaceholder'         => $this->wpService->__('Search location...', 'modularity-frontend-form'),
            'nameOfTheLocation'         => $this->wpService->__('Name of the location', 'modularity-frontend-form'),
            'removeRow'                 => $this->wpService->__('Remove row', 'modularity-frontend-form'),
            'atLeastOneValueIsRequired' => $this->wpService->__('At least one value is required', 'modularity-frontend-form'),
            'loading'                   => $this->wpService->__('Loading', 'modularity-frontend-form'),
            'newRow'                    => $this->wpService->__('New row', 'modularity-frontend-form'),

            // Error Messages for fields
            'errorRequired'            => $this->wpService->__('This field is required', 'modularity-frontend-form'),
            'errorEmail'               => $this->wpService->__('Please enter a valid email address','modularity-frontend-form'),
            'errorUrl'                 => $this->wpService->__('Please enter a valid URL (ex. https://website.com)', 'modularity-frontend-form'),
            'errorPhone'               => $this->wpService->__('Please enter a valid phone number', 'modularity-frontend-form'),
            'errorDate'                => $this->wpService->__('Please enter a valid date', 'modularity-frontend-form'),
            'errorDateTime'            => $this->wpService->__('Please enter a valid date and time', 'modularity-frontend-form'),
            'errorTime'                => $this->wpService->__('Please enter a valid time', 'modularity-frontend-form'),
            'errorNumber'              => $this->wpService->__('Please enter a valid number', 'modularity-frontend-form'),

            'statusTitleLoading'       => $this->wpService->__('Loading', 'modularity-frontend-form'),
            'statusTitleError'         => $this->wpService->__('Error', 'modularity-frontend-form'),
            'statusTitleSuccess'       => $this->wpService->__('Success', 'modularity-frontend-form'),
            'statusTitleSubmitting'    => $this->wpService->__('Submitting', 'modularity-frontend-form'),

            'submitting'               => $this->wpService->__('Submitting', 'modularity-frontend-form'),
            'submitInit'               => $this->wpService->__('Preparing', 'modularity-frontend-form'),
            'submitSuccess'            => $this->wpService->__('Form submitted successfully.', 'modularity-frontend-form'),
            'submitUrlError'           => $this->wpService->__('Form submission failed: Cound not find path.', 'modularity-frontend-form'),
            'submitError'              => $this->wpService->__('An error occurred while submitting the form.', 'modularity-frontend-form'),

            'communicationError'       => $this->wpService->__('Communication error', 'modularity-frontend-form'),

            'nonceRequest'             => $this->wpService->__('Securing', 'modularity-frontend-form'),
            'nonceUrlMissing'          => $this->wpService->__('Could not secure connection – link missing.', 'modularity-frontend-form'),
            'nonceRequestSuccess'      => $this->wpService->__('Securing', 'modularity-frontend-form'),
            'nonceRequestFailed'       => $this->wpService->__('Could not secure connection – please try again.', 'modularity-frontend-form'),
        ];
    }

    public function template(): string
    {
        return 'frontend-form.blade.php';
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
        if (!$this->hasModule()) {
            return;
        }

        $this->wpService->wpRegisterStyle(
            $this->getScriptHandle(),
            MODULARITYFRONTENDFORM_URL . '/dist/' . 
            $this->cacheBust->name('css/main.css')
        );

        $this->wpService->wpEnqueueStyle($this->getScriptHandle());
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
        if (!$this->hasModule()) {
            return;
        }
        // Register the script
        $this->wpService->wpRegisterScript(
            $this->getScriptHandle(),
            MODULARITYFRONTENDFORM_URL . '/dist/' . 
            $this->cacheBust->name('js/init.js')
        );

        $this->addAttributesToScriptTag($this->getScriptHandle(), [
            'type' => 'module'
        ]);
        
        // Language strings
        $this->wpService->wpLocalizeScript(
            $this->getScriptHandle(),
            'modularityFrontendFormLang',
            (array) $this->getLang() ?? []
        );

        // MiscData thats needed in frontend
        $data = $this->getScriptData();
        $data = json_encode($data);
        $this->wpService->wpAddInlineScript(
            $this->getScriptHandle(),
            'var modularityFrontendFormData = ' . $data . ';',
            'before'
        );

        // Enqueue the script
        $this->wpService->wpEnqueueScript($this->getScriptHandle());
    }

    /**
     * Retrieves the script data.
     *
     * This method retrieves the script data by applying filters to the data array.
     *
     * @return array The script data.
     */
    private function getScriptData(): array
    {
        return $this->wpService->applyFilters(
            'Modularity/Module/FrontendForm/Assets/Data', 
            [
                'placeSearchApiUrl' => $this->wpService->getRestUrl(null, 'placesearch/v1/openstreetmap'),
            ]
        ); 
    }

    /**
     * Retrieves the script handle.
     *
     * This method retrieves the script handle for the form.
     *
     * @param string|null $suffix The suffix to append to the script handle.
     * @return string The script handle.
     */
    private function getScriptHandle($suffix = null): string
    {
        return 'modularity-' . $this->slug . ($suffix ? '-' . $suffix : '');
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

    /**
     * Add attributes to the script tag for a given handle.
     *
     * @param string $handle The handle of the script to modify.
     * @param array $attributes Key-value pairs of attributes to add to the script tag.
     * @return void
     */
    private function addAttributesToScriptTag(string $handle, array $attributes): void {
        $this->wpService->addFilter('script_loader_tag', function($tag, $tag_handle) use ($handle, $attributes) {
            if ($tag_handle === $handle) {
                foreach ($attributes as $key => $value) {
                    $tag = str_replace(' src=', sprintf(' %s="%s" src=', esc_attr($key), esc_attr($value)), $tag);
                }
            }
            return $tag;
        }, 10, 2);
    }

    /**
     * Available "magic" methods for modules:
     * init()            What to do on initialization
     * data()            Use to send data to view (return array)
     * style()           Enqueue style only when module is used on page
     * script            Enqueue script only when module is used on page
     * adminEnqueue()    Enqueue scripts for the module edit/add page in admin
     * template()        Return the view template (blade) the module should use when displayed
     */
}
