import AsyncNonce from "../../asyncNonce/asyncNonce";
import StatusHandler from "../../formStatus/handler";
import StatusRenderer from "../../formStatus/render";
import SubmitStatus from "../../formStatus/enum";
import SubmitInterface from "./submitInterface";
import Form from "../../form/form";
import FormMode from "../../form/formModeEnum";

class Submit implements SubmitInterface {
    constructor(
      private form: Form,
      private modularityFrontendFormData: ModularityFrontendFormData,
      private modularityFrontendFormLang: ModularityFrontendFormLang,
      private asyncNonce: AsyncNonce,
      private statusHandler: StatusHandler,
      private statusRenderer: StatusRenderer,
    ) {}
    
    /**
     * Setup the submit functionality.
     * @param form The form element to be submitted.
     * 
     * @returns A promise that resolves when the form is submitted.
     */
    public async submit(): Promise<void> {

      // Render the submit status when changed
      this.statusRenderer.setup();

      // Set the initial status to working
      this.statusHandler.setStatus(
        SubmitStatus.Working,
        this.modularityFrontendFormLang?.submitInit ?? "Submitting your form, please wait...",
        'send',
        10
      );

      // Respect the form mode, set endpoint accordingly
      const url = this.form.mode === FormMode.Post
        ? this.modularityFrontendFormData.apiRoutes?.submitForm
        : this.form.mode === FormMode.Update
        ? this.modularityFrontendFormData.apiRoutes?.updateForm
        : null;
    
      if (!url) {
        this.statusHandler.setStatus(
          SubmitStatus.Error,
          this.modularityFrontendFormLang?.submitUrlError ?? "Could not find the submit URL. Please check your configuration.",
          'link_off',
          0
        );
        return;
      }

      //Init nonce by fetching nonce from endpoint and injecting it into the form
      await this.asyncNonce.setup(this.form.formElement, this.statusHandler);
    
      try {

        //Get data-js-frontend-form-id 
        const formId = this.form.formElement.getAttribute("data-js-frontend-form-id");
        if (!formId) {
          this.statusHandler.setStatus(
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
          body: new FormData(this.form.formElement)
        });
    
        const json = await response.json();

        // Failed
        if (!response.ok) {
          if(json.code == "invalid_field_values") {
            this.statusHandler.setStatus(
              SubmitStatus.Error,
              json?.message ?? this.modularityFrontendFormLang?.submitError,
              'data_alert',
              0,
              2000
            );
            return;
          }


          this.statusHandler.setStatus(
            SubmitStatus.Error,
            json?.message ?? this.modularityFrontendFormLang?.submitError,
            'error',
            0,
            3000
          );
        }
    
        // Success
        if(response.ok) {
          this.statusHandler.setStatus(
            SubmitStatus.Success,
            this.modularityFrontendFormLang?.submitSuccess ?? "Form submitted successfully! Thank you for your submission.",
            'celebration',
            100,
            4000
          );
        }

      } catch (error: any) {
        this.statusHandler.setStatus(
          SubmitStatus.Error,
          this.modularityFrontendFormLang?.submitError ?? "Form submission failed. Please try again." + (error?.message ? ` (${error.message})` : ""),
          'error',
          0,
          4000
        );
      }
    }
}
  export default Submit;