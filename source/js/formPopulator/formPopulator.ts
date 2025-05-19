type Token32 = string & { __lengthBrand: 32 };

interface FormParams {
  moduleId: number;
  postId: number;
  token: Token32;
}

class FormPopulator {
  private formParams: FormParams | null = null;

  constructor(
    private formContainer: HTMLElement, 
    private form: HTMLFormElement,
    private modularityFrontendFormData: ModularityFrontendFormData,
    private modularityFrontendFormLang: ModularityFrontendFormLang,
  ) {
    this.formParams = this.extractParamsFromUrl();
  }

  /**
   * Extracts the form parameters from the URL.
   * @returns An object containing the module ID, post ID, and token, or null if not found.
   */
  private extractParamsFromUrl(): FormParams | null {
    const params    = new URLSearchParams(window.location.search);
    const postIdRaw = params.get('postId');
    const tokenRaw  = params.get('token');
    const postId    = Number(postIdRaw);
    const moduleId  = Number(this.form.getAttribute("data-js-frontend-form-id") || "");
  
    if (!isNaN(postId) && tokenRaw && tokenRaw.length === 32) {
      return {
        moduleId: moduleId as number,
        postId: postId as number,
        token: tokenRaw as Token32
      };
    }
    return null;
  }

  /**
   * Initializes the form populator by fetching and populating the form data.
   */
  public async initialize(): Promise<void> {
    if (this.formParams) {
      const formData = await this.get(this.formParams.postId, this.formParams.token);
      console.log("Form data fetched:", formData);

      if (formData) {
        this.populateForm(formData);
      }
    }
  }

  /**
   * Fetches the form data from the server.
   * @param postId The ID of the post.
   * @param token The security validation key.
   * @returns The form data as a string or null if not found.
   */
  public async get(postId: number, token: Token32): Promise<string | null> {
    let url = this.modularityFrontendFormData?.apiRoutes?.getForm;

    if (!url) {
      console.error("Could not find the form data URL. Please check your configuration.");
      return null;
    }

    url = (() => {
      const { form } = this;
      const urlBuilder = new URL(url);
      const params = new URLSearchParams({
        'post-id': postId.toString(),
        'token': token,
        'module-id': form?.getAttribute('data-js-frontend-form-id') || '',
      });
    
      urlBuilder.search = params.toString();
      
      return urlBuilder.toString();
    })();

    try {

      const response = await fetch(url);
      if (!response.ok) {
        throw new Error(`HTTP error: ${response.status}`);
      }

      const json = await response.json();
      return json?.data ?? null;
    } catch (error: any) {
      console.error(`Error fetching form data: ${error.message}`);
      return null;
    }
    
    return null;
  }

  /**
   * Populates the form with the provided data.
   * @param formData The data to populate the form with.
   */
  private populateForm(formData: any): void {
    if (!this.form) return; 
    for (const fieldName in formData) {
      const fieldValue = formData[fieldName];
      this.populateField(fieldName, fieldValue);
    }
  }

  /**
   * Populates a specific field in the form with the provided value.
   * @param fieldName The name of the field to populate.
   * @param value The value to set for the field.
   */
  private populateField(fieldName: string, value: any): void {

    const fieldSelector = `[name="mod-frontend-form[${fieldName}]"]`;
    const field = this.form.querySelector(fieldSelector) as HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement | null;

    if (!field) return;

    if (field instanceof HTMLInputElement) {
      if (field.type === 'checkbox') {
        field.checked = Boolean(value);
      } else if (field.type === 'radio') {
        const radio = this.form.querySelector(`${fieldSelector}[value="${value}"]`) as HTMLInputElement | null;
        if (radio) radio.checked = true;
      } else {
        field.value = value;
      }
    } else if (field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement) {
      field.value = value;
    }
  }

  /**
   * Validates the form after form is populated.
   * @returns True if the form is valid, false otherwise.
   */
  private validateForm(): boolean {
    return true;
    /*if (!this.formElement) {
      return false;
    }
    return this.formElement.checkValidity();*/ 

  }
}

export default FormPopulator;