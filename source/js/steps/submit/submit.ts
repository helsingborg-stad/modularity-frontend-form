import AsyncNonce from "../../asyncNonce/asyncNonce";
import SubmitStatusHandler from "./status/handler";
import SubmitStatusRenderer from "./status/render";
import SubmitStatus from "./status/enum";
import AsyncNonceInterface from "../../asyncNonce/asyncNonceInterface";
import SubmitInterface from "./submitInterface";

class Submit implements SubmitInterface {
    constructor(
      private form: HTMLFormElement,
      private modularityFrontendFormData: ModularityFrontendFormData,
      private asyncNonce: AsyncNonce,
      private submitStatusHandler: SubmitStatusHandler = new SubmitStatusHandler(form),
      private submitStatusRenderer: SubmitStatusRenderer = new SubmitStatusRenderer(form),
    ) {}
    
    public async submit(): Promise<void> {

      // Render the submit status when changed
      this.submitStatusRenderer.setup();

      // Set the initial status to working
      this.submitStatusHandler.setStatus(
        SubmitStatus.Working,
        "Hey, im submitting now! Please wait a moment..."
      );

      const url = this.modularityFrontendFormData.apiRoutes?.submitForm;
    
      if (!url) {
        this.submitStatusHandler.setStatus(
          SubmitStatus.Error,
          "Could not find the submit URL. Please check your configuration."
        );
        return;
      }

      //Init nonce by fetching nonce from endpoint and injecting it into the form
      await this.asyncNonce.setup(this.form, this.submitStatusHandler);
    
      try {
        const response = await fetch(url, {
          method: "POST",
          body: new FormData(this.form),
        });
    
        if (!response.ok) {
          throw new Error(`Response status: ${response.status}`);
        }
    
        const json = await response.json();
        console.log("Form submitted successfully:", json);

        // Set the status to success
        this.submitStatusHandler.setStatus(
          SubmitStatus.Success,
          "Form submitted successfully! Thank you for your submission."
        );
      } catch (error: any) {
        console.error("Form submission failed:", error?.message || error);
      }
    }
}
  export default Submit;