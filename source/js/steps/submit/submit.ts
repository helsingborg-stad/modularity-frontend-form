import AsyncNonce from "../../asyncNonce/asyncNonce";
interface SubmitInterface {
    submit(event: Event): void | Promise<void>;
}

class Submit implements SubmitInterface {
    constructor(
      private form: HTMLFormElement,
      private modularityFrontendFormData: ModularityFrontendFormData,
      private asyncNonce: AsyncNonce
    ) {}
    
  
    public async submit(): Promise<void> {
      console.log("Submitting form...");
      console.log(this.modularityFrontendFormData.apiRoutes); 
      const url = this.modularityFrontendFormData.apiRoutes?.submitForm;
    
      if (!url) {
        console.error("Submit URL is not defined.");
        return;
      }

      //Init nonce by fetching nonce from endpoint and injecting it into the form
      await this.asyncNonce.setup(this.form);
    
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
      } catch (error: any) {
        console.error("Form submission failed:", error?.message || error);
      }
    }
}
  export default Submit;