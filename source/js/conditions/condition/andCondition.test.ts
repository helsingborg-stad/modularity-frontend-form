import { describe, expect, it } from '@jest/globals';
import AndCondition from './andCondition';

describe('AND Condition', () => {
    const conditionWithoutClass: Condition = {
        field: 'testField',
        class: null,
        value: null,
        operator: '',
    };

    const andConditionWithoutClass = new AndCondition([conditionWithoutClass, conditionWithoutClass]);

    it('getConditionFieldNames() returns an array of field names from provided Condition[]', () => {
        expect(andConditionWithoutClass.getConditionFieldNames()).toEqual(['testField', 'testField']);
    });

    it('getConditions() returns Condition[]', () => {
        expect(andConditionWithoutClass.getConditions()).toEqual([conditionWithoutClass, conditionWithoutClass]);
    });

    it('validate() returns true if no class', () => {
        expect(andConditionWithoutClass.validate()).toEqual(true);
    });

    it('validate() returns false if field is already disabled', () => {
        const andOperatorWithClass = new AndCondition([createConditionWithClass(true, true)]);
        expect(andOperatorWithClass.validate()).toEqual(false);
    });

    it('validate() returns false if ANY field is already disabled', () => {
        const andOperatorWithClass = new AndCondition([createConditionWithClass(false, true), createConditionWithClass(true, true)]);
        expect(andOperatorWithClass.validate()).toEqual(false);
    });

    it('validate() returns false if field validation failed', () => {
        const andOperatorWithClass = new AndCondition([createConditionWithClass(false, false)]);
        expect(andOperatorWithClass.validate()).toEqual(false);
    });

    it('validate() returns false if ANY field validation failed', () => {
        const andOperatorWithClass = new AndCondition([createConditionWithClass(false, true), createConditionWithClass(false, false)]);
        expect(andOperatorWithClass.validate()).toEqual(false);
    });
    
    it('validate() returns empty array if conditions are missing a name', () => {
        const andOperatorWithClass = new AndCondition([createConditionWithClass(false, true, null)]);
        expect(andOperatorWithClass.getConditionFieldNames()).toEqual([]);
    });
});

function createConditionWithClass(isDisabled: boolean, isValid: boolean, field: null|string = 'testField'): Condition {
    return {
        field: field,
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