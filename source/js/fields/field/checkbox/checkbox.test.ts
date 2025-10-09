import { describe, expect, it } from '@jest/globals';
import Checkbox from './checkbox';
import CheckboxConditionsHandler from './condition/checkboxConditionsHandler';
import CheckboxConditionValidator from './condition/checkboxConditionValidator';

describe('Checkbox', () => {
    const fakeCheckBoxHandler = {
        init: jest.fn(),
        dispatchUpdateEvent(condition: any): boolean { return true; },
        addValueChangeListener(field: FieldInterface): void {},
        getConditions(): Condition[] { return []; },
        getIsDisabled(): boolean { return false; },
        validate(): void {}
    } as unknown as CheckboxConditionsHandler;
    const fakeCheckBoxValidator = {
        init: jest.fn(),
        validate(condition: Condition): boolean { return true; }
    } as unknown as CheckboxConditionValidator;
    const fakeValidator = {
        init: jest.fn(),
        validate: jest.fn()
    } as unknown as FieldValidatorInterface;
    
    const field = document.createElement('div');
    field.innerHTML = `<input type="checkbox" name="testField" value="1" checked><input type="checkbox" name="testField2" value="2">`;
    const choices = field.querySelectorAll('input[type="checkbox"]') as NodeListOf<HTMLInputElement>;

    const checkBox = new Checkbox(
        field,
        choices,
        'testField',
        fakeCheckBoxValidator,
        fakeCheckBoxHandler,
        fakeValidator // <-- Add this argument
    )

    it('init() initializes the conditions handler and validator', () => {
        checkBox.init({} as any);
        expect(fakeCheckBoxHandler.init).toHaveBeenCalledWith(checkBox, {} as any);
        expect(fakeCheckBoxValidator.init).toHaveBeenCalledWith(checkBox);
        expect(fakeValidator.init).toHaveBeenCalledWith(checkBox);
    });

    it('getName() returns field name', () => {
        expect(checkBox.getName()).toBe('testField');
    });

    it('getConditionsHandler() returns the conditions handler', () => {
        expect(checkBox.getConditionsHandler()).toBe(fakeCheckBoxHandler);
    });

    it('getConditionValidator() returns the validation handler', () => {
        expect(checkBox.getConditionValidator()).toBe(fakeCheckBoxValidator);
    });
    
    it('getChoices() returns the available choices', () => {
        expect(checkBox.getChoices()).toBe(choices);
    });
    
    it('getFieldContainer() returns the available choices', () => {
        expect(checkBox.getFieldContainer()).toBe(field);
    });

    it('getSelectedChoices() returns selected choices values', () => {
        expect(checkBox.getSelectedChoices()).toStrictEqual(["1"]);
    });
});