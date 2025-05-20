import AsyncNonce from "../asyncNonce/asyncNonce";
import StatusHandler from "../formStatus/handler";
import StatusRenderer from "../formStatus/render";
import SubmitStatus from "../formStatus/enum";
import { TypedFormElement } from "../form/form";

type Token32 = string & { __lengthBrand: 32 };

interface FormParams {
  moduleId: number;
  postId: number;
  token: Token32;
}

class FormPopulator {
  private formParams: FormParams | null = null;

  constructor(
    private typedFormElement: TypedFormElement,
    private modularityFrontendFormData: ModularityFrontendFormData,
    private modularityFrontendFormLang: ModularityFrontendFormLang,
    private asyncNonce: AsyncNonce,
    private statusHandler: StatusHandler,
    private statusRenderer: StatusRenderer,
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
    const moduleId  = Number(this.typedFormElement.formElement.getAttribute("data-js-frontend-form-id") || "");
  
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

    // Render the submit status when changed
    this.statusRenderer.setup();

    
    if (this.formParams) {

      // Set the initial status to working
      this.statusHandler.setStatus(
        SubmitStatus.Loading,
        "Loading form data...",
        'file_open',
        0,
        1000
      );

      const formData = await this.get(this.formParams.postId, this.formParams.token);
      if (formData) {
        this.populateForm(formData);
        this.validateForm();
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
      this.statusHandler.setStatus(
        SubmitStatus.Loading,
        "Could not find url.",
        'file_open',
        100,
        2000
      );
      return null;
    }

    url = (() => {
      const { typedFormElement } = this;
      const urlBuilder = new URL(url);
      const params = new URLSearchParams({
        'post-id': postId.toString(),
        'token': token,
        'module-id': typedFormElement.formElement?.getAttribute('data-js-frontend-form-id') || '',
      });
    
      urlBuilder.search = params.toString();

      return urlBuilder.toString();
    })();

    try {
      const response  = await fetch(url);
      const json      = await response.json();

      if (!response.ok) {
        this.statusHandler.setStatus(
          SubmitStatus.Error,
          json.message ?? this.modularityFrontendFormLang?.communicationError ?? "Communication error.",
          'vpn_key_alert',
          0,
          10000
        );
        return null;
      }

      this.statusHandler.setStatus(
        SubmitStatus.Loading,
        "Loading form data...",
        'file_open',
        100,
        500
      );

      return json?.data ?? null;
      
    } catch (error: any) {
      this.statusHandler.setStatus(
        SubmitStatus.Error,
        this.modularityFrontendFormLang?.communicationError ?? "Communication error.",
        'link_off',
        0,
        10000
      );
      return null;
    }
  }

  /**
   * Populates the form with the provided data.
   * @param formData The data to populate the form with.
   */
  private populateForm(formData: any): boolean {
    if (!this.typedFormElement.formElement) {
      return false
    }
    for (const fieldName in formData) {
      this.populateField(
        fieldName, 
        formData[fieldName] ?? ''
      );
    }
    return true;
  }

  /**
   * Populates a specific field in the form with the provided value.
   * @param fieldName The name of the field to populate.
   * @param value The value to set for the field.
   */
  private populateField(fieldName: string, value: any): void {
    const fieldSelector = `[name="mod-frontend-form[${fieldName}]"]`;
    const field         = this.typedFormElement.formElement.querySelector(fieldSelector) as HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement | null;

    if (!field) {
      console.warn(`Field not found: ${fieldSelector}`);
      return;
    }

    switch (true) {
      case field instanceof HTMLInputElement && field.type === 'checkbox':
        this.populateCheckbox(field, value);
        break;
      case field instanceof HTMLInputElement && field.type === 'radio':
        this.populateRadio(fieldName, value);
        break;
      case field instanceof HTMLInputElement:
        this.populateInput(field, value);
        break;
      case field instanceof HTMLSelectElement:
        this.populateSelect(field, value);
        break;
      case field instanceof HTMLTextAreaElement:
        this.populateTextarea(field, value);
        break;
    }
  }

  /**
   * Populates an input field with the provided value.
   * @param field The input element to populate.
   * @param value The value to set for the input.
   */
  private populateInput(field: HTMLInputElement, value: any): void {
    field.value = value;
  }

  /**
   * Populates a checkbox field with the provided value.
   * @param field The checkbox element to populate.
   * @param value The value to set for the checkbox.
   */
  private populateCheckbox(field: HTMLInputElement, value: any): void {
    field.checked = Boolean(value);
  }

  /**
   * Populates a radio button field with the provided value.
   * @param fieldName The name of the radio button group.
   * @param value The value to set for the radio button.
   */
  private populateRadio(fieldName: string, value: any): void {
    const selector  = `[name="mod-frontend-form[${fieldName}]"][value="${value}"]`;
    const radio     = this.typedFormElement.form.querySelector(selector) as HTMLInputElement | null;
    
    if (radio) {
      radio.checked = true;
    }
  }

  /**
   * Populates a select field with the provided value.
   * @param field The select element to populate.
   * @param value The value to set for the select.
   */
  private populateSelect(field: HTMLSelectElement, value: any): void {
    field.value = value;
  }

  /**
   * Populates a textarea field with the provided value.
   * @param field The textarea element to populate.
   * @param value The value to set for the textarea.
   */
  private populateTextarea(field: HTMLTextAreaElement, value: any): void {
    field.value = value;
  }

  /**
   * Validates the form after form is populated.
   * @returns True if the form is valid, false otherwise.
   */
  private validateForm(): boolean {
    if (!this.typedFormElement.formElement) {
      return false;
    }
    return this.typedFormElement.form.checkValidity();
  }
}

export default FormPopulator;