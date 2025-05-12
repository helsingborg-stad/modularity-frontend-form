import { describe, expect, it } from '@jest/globals';
import CheckboxConditionsHandler from './checkboxConditionsHandler';

function createChoicesAndField(): [HTMLElement, NodeListOf<HTMLInputElement>] {
    const field = document.createElement('div');
    field.innerHTML = `
        <input type="checkbox" name="testField" value="1" checked>
        <input type="checkbox" name="testField2" value="2">
    `;
    const choices = field.querySelectorAll('input[type="checkbox"]') as NodeListOf<HTMLInputElement>;
    return [field, choices];
}

describe('CheckboxConditionsHandler - Public Methods (manual mocks)', () => {
    const [field, choices] = createChoicesAndField();

    const fakeCheckbox = {
        getChoices(): NodeListOf<HTMLInputElement> {
            return choices;
        },
        getField(): HTMLElement {
            return field;
        }
    } as CheckboxInterface;

    it('getIsDisabled() returns false initially and true after validate() disables the field', () => {
        let validateCalled = false;
    
        const conditionMock: ConditionInterface = {
            validate() {
                validateCalled = true;
                return false; // Simulate invalid condition
            },
            getConditions() {
                return [];
            },
            getConditionFieldNames() {
                return [];
            }
        };
    
        const handler = new CheckboxConditionsHandler("");
        handler.init(fakeCheckbox, {
            build() {
                return [conditionMock];
            }
        });
    
        expect(handler.getIsDisabled()).toBe(false);
    
        handler.validate();
    
        expect(validateCalled).toBe(true);
        expect(handler.getIsDisabled()).toBe(true);
    });

    it('getConditions() returns the correct list', () => {
        const conditionMock: ConditionInterface = {
            validate() {
              return true; // or false depending on the test
            },
            getConditions() {
              return [];
            },
            getConditionFieldNames() {
              return [];
            }
        };

        const handler = new CheckboxConditionsHandler("");
        handler.init(fakeCheckbox, {
            build() {
                return [conditionMock];
            }
        });

        const result = handler.getConditions();
        expect(result.length).toBe(1);
        expect(result[0]).toBe(conditionMock);
    });

    it('addValueChangeListener() triggers validate() on change', () => {
        let validateCalled = 0;

        const mockField = {
            getName() {
                return 'mockField';
            },
            getConditionsHandler() {
                return {
                    validate() {
                        validateCalled++;
                    }
                };
            }
        } as FieldInterface;

        const handler = new CheckboxConditionsHandler("");
        handler.init(fakeCheckbox, {
            build() {
                return [];
            }
        });

        handler.addValueChangeListener(mockField);

        // Trigger change event on first checkbox
        choices[0].dispatchEvent(new Event('change'));

        expect(validateCalled).toBe(1);
    });

    it('validate() enables field when at least one condition returns true', () => {
        const handler = new CheckboxConditionsHandler("");
        const conditionMock: ConditionInterface = {
            validate() {
              return true; // or false depending on the test
            },
            getConditions() {
              return [];
            },
            getConditionFieldNames() {
              return [];
            }
          };

        handler.init(fakeCheckbox, {
            build() {
                return [conditionMock];
            }
        });

        // Manually disable first
        (handler as any).updateDisabled(true);
        expect(handler.getIsDisabled()).toBe(true);

        // Then re-enable
        handler.validate();
        expect(handler.getIsDisabled()).toBe(false);
    });
});

function createConditionMock() {
    return  {
        validate() {
          return true; // or false depending on the test
        },
        getConditions() {
          return [];
        },
        getConditionFieldNames() {
          return [];
        }
    } as ConditionInterface;
}