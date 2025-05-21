import FormMode  from './formModeEnum';

export interface FormInterface {
  get mode(): FormMode;
  set mode(value: FormMode);
  formElement: HTMLFormElement;
  formElementContainer: HTMLElement;
}