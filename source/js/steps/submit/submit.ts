import AsyncNonce from "../../asyncNonce/asyncNonce";
import SubmitStatusHandler from "./status/handler";
import SubmitStatusRenderer from "./status/render";
import SubmitStatus from "./status/enum";
import SubmitInterface from "./submitInterface";

class Submit implements SubmitInterface {
    constructor(
      private form: HTMLFormElement,
      private modularityFrontendFormData: ModularityFrontendFormData,
      private modularityFrontendFormLang: ModularityFrontendFormLang,
      private asyncNonce: AsyncNonce,
      private submitStatusHandler: SubmitStatusHandler,
      private submitStatusRenderer: SubmitStatusRenderer,
    ) {}
    
    /**
     * Setup the submit functionality.
     * @param form The form element to be submitted.
     * 
     * @returns A promise that resolves when the form is submitted.
     */
    public async submit(): Promise<void> {

      // Render the submit status when changed
      this.submitStatusRenderer.setup();

      // Set the initial status to working
      this.submitStatusHandler.setStatus(
        SubmitStatus.Working,
        this.modularityFrontendFormLang?.submitInit ?? "Submitting your form, please wait...",
        'send',
        10
      );

      const url = this.modularityFrontendFormData.apiRoutes?.submitForm;
    
      if (!url) {
        this.submitStatusHandler.setStatus(
          SubmitStatus.Error,
          this.modularityFrontendFormLang?.submitUrlError ?? "Could not find the submit URL. Please check your configuration.",
          'link_off',
          0
        );
        return;
      }

      //Init nonce by fetching nonce from endpoint and injecting it into the form
      await this.asyncNonce.setup(this.form, this.submitStatusHandler);
    
      try {

        //Get data-js-frontend-form-id 
        const formId = this.form.getAttribute("data-js-frontend-form-id");
        if (!formId) {
          this.submitStatusHandler.setStatus(
            SubmitStatus.Error,
            this.modularityFrontendFormLang?.submitError ?? "Could not find the form ID. Please check your configuration.",
            'link_off',
            0
          );
          return;
        }
        
        const urlWithParams = new URL(url);
        urlWithParams.searchParams.append("module-id", formId);
        const finalUrl = urlWithParams.toString();

        const response = await fetch(finalUrl, {
          method: "POST",
          body: new FormData(this.form)
        });
    
        if (!response.ok) {
          throw new Error(`Response status: ${response.status}`);
        }
    
        const json = await response.json();

        // Set the status to success
        this.submitStatusHandler.setStatus(
          SubmitStatus.Success,
          this.modularityFrontendFormLang?.submitSuccess ?? "Form submitted successfully! Thank you for your submission.",
          'celebration',
          100
        );
      } catch (error: any) {
        this.submitStatusHandler.setStatus(
          SubmitStatus.Error,
          this.modularityFrontendFormLang?.submitError ?? "Form submission failed. Please try again." + (error?.message ? ` (${error.message})` : ""),
          'error',
          0
        );
      }
    }
}
  export default Submit;