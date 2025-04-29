import { describe, expect, it } from '@jest/globals';
import OrCondition from './orCondition';

describe('OR Condition', () => {
    it('getConditionFieldNames() returns an array of field names from provided Condition', () => {
        const condition: Condition = {
            field: 'testField',
            class: null,
            value: null,
            operator: '',
        };

        const orCondition = new OrCondition(condition);
        const fieldNames = orCondition.getConditionFieldNames();

        expect(fieldNames).toEqual(['testField']);
    });
});