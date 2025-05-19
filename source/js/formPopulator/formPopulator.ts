type Token32 = string & { __lengthBrand: 32 };

interface FormParams {
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

  private extractParamsFromUrl(): FormParams | null {
    const params    = new URLSearchParams(window.location.search);
    const postIdRaw = params.get('postId');
    const tokenRaw  = params.get('token');
    const postId    = Number(postIdRaw);
  
    if (!isNaN(postId) && tokenRaw && tokenRaw.length === 32) {
      return {
        postId,
        token: tokenRaw as Token32
      };
    }
    return null;
  }

  public initialize(): void {
    if (this.formParams) {
      let formData = this.get(this.formParams.postId, this.formParams.token);

      if (formData) {
        this.populateForm(formData);
      }
    }
  }

  public async get(postId: number, token: Token32): Promise<string | null> {
    let url = this.modularityFrontendFormData?.apiRoutes?.getForm;

    if (!url) {
      console.error("Could not find the form data URL. Please check your configuration.");
      return null;
    }

    url = (() => { 
      const urlBuilder = new URL(url); 
      urlBuilder.searchParams.append("post-id", postId.toString()); 
      urlBuilder.searchParams.append("token", token); 
      return urlBuilder.toString(); 
    })();

    try {

      const response = await fetch(url);
      if (!response.ok) {
        throw new Error(`HTTP error: ${response.status}`);
      }

      const json = await response.json();
      return json?.formData ?? null;
    } catch (error: any) {
      console.error(`Error fetching form data: ${error.message}`);
      return null;
    }
    
    return null;
  }

  private populateForm(formData: any): void {
    if (!this.form) return;
    for (const fieldName in formData) {
      const fieldValue = formData[fieldName];
      this.populateField(fieldName, fieldValue);
    }
  }

  private populateField(fieldName: string, value: any): void {
    /*if (!this.formElement) return;
    const field = this.formElement.querySelector(`[name="${fieldName}"]`) as HTMLInputElement | null;
    if (field) {
      field.value = value;
    }*/ 
  }

  private validateForm(): boolean {
    return true;
    /*if (!this.formElement) {
      return false;
    }
    return this.formElement.checkValidity();*/ 

  }
}

export default FormPopulator;