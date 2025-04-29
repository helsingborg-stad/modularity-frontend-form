import { describe, expect, it } from '@jest/globals';
import OrCondition from './orCondition';

describe('OR Condition', () => {
    const conditionWithoutClass: Condition = {
        field: 'testField',
        class: null,
        value: null,
        operator: '',
    };

    const orConditionWithoutClass = new OrCondition(conditionWithoutClass);

    it('getConditionFieldNames() returns an array of field names from provided Condition', () => {
        expect(orConditionWithoutClass.getConditionFieldNames()).toEqual(['testField']);
    });

    it('getConditions() returns an array of Conditions', () => {
        expect(orConditionWithoutClass.getConditions()).toEqual([conditionWithoutClass]);
    });

    it('validate() returns true if no class', () => {
        expect(orConditionWithoutClass.validate()).toEqual(true);
    });

    it('validate() returns false if field is already disabled', () => {
        const orCondition = new OrCondition(createConditionWithClass(true, true));
        expect(orCondition.validate()).toEqual(false);
    });

    it('validate() returns true if is not disabled and condition is valid', () => {
        const orCondition = new OrCondition(createConditionWithClass(false, true));
        expect(orCondition.validate()).toEqual(true);
    });

    it('validate() returns false if validation failed', () => {
        const orCondition = new OrCondition(createConditionWithClass(false, false));
        expect(orCondition.validate()).toEqual(false);
    });
});

function createConditionWithClass(isDisabled: boolean, isValid: boolean): Condition {
    return {
        field: 'testField',
        class: {
            getConditionsHandler() {
                return {
                    getIsDisabled() {
                        return isDisabled;
                    }
                } as ConditionsHandlerInterface;
            },
            getConditionValidator() {
                return {
                    validate(condition: Condition) {
                        return isValid;
                    }
                } as ConditionValidatorInterface;
            },
        } as FieldInterface,
        value: null,
        operator: '',
    } as Condition;
}