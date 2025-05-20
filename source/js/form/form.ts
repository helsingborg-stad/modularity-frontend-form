export enum FormMode {
  Post    = 'post',
  Update  = 'update'
}

export class TypedFormElement {
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
    this._mode = value;
  }

  get formElement(): HTMLFormElement {
    return this.form;
  }

  get formElementContainer(): HTMLElement { 
    return this.form.closest('.modularity-frontend-form') as HTMLElement;
  }
}