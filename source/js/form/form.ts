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
}
export default Form;