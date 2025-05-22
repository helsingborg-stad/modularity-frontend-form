import FormMode from './formModeEnum';
export class Form {
  private _mode: FormMode;
  public form: HTMLFormElement;

  constructor(form: HTMLFormElement, mode: FormMode) {
    this.form = form;
    this._mode = mode;
  }

  get mode(): FormMode {
    return this._mode;
  }

  set mode(value: FormMode) {
    this.form.setAttribute('data-form-mode', value);
    this._mode = value;
  }

  get formElement(): HTMLFormElement {
    return this.form;
  }

  get formElementContainer(): HTMLElement { 
    return this.form.closest('[data-js-frontend-form]') as HTMLElement;
  }

  get formId(): number {
    const formId = this.form.getAttribute('data-js-frontend-form-id');
    if (formId) {
      return parseInt(formId);
    }
    throw new Error('Form ID not found');
  }

  get formUpdateId(): number | null {
    if(this.mode !== FormMode.Update) {
      return null;
    }
    const urlParams = new URLSearchParams(window.location.search);
    const postId = urlParams.get('postId');
    if (postId) {
      return parseInt(postId);
    }
    throw new Error('Post ID not found in URL');
  }
}
export default Form;