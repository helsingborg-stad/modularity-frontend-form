import { describe, expect, it } from '@jest/globals';
import ConditionBuilder from './conditionBuilder';
import NullCondition from './condition/nullCondition';
import OrCondition from './condition/orCondition';
import AndCondition from './condition/andCondition';

describe('Condition Builder', () => {
    const conditionWithoutClass: Condition = {
        field: 'testField',
        class: null,
        value: null,
        operator: '',
    };

    const conditionBuilder = new ConditionBuilder({
        'testField': {
        } as FieldInterface,
        'testField2': {
        } as FieldInterface,
    });

    it('build() returns an array with a NullCondition if no conditions or faulty value are provided', () => {
        const nullConditionEmptyArray  = conditionBuilder.build([]);
        const nullConditionFaultyValue = conditionBuilder.build(null);
        expect(nullConditionEmptyArray).toHaveLength(1);
        expect(nullConditionEmptyArray[0]).toBeInstanceOf(NullCondition);
        expect(nullConditionFaultyValue).toHaveLength(1);
        expect(nullConditionFaultyValue[0]).toBeInstanceOf(NullCondition);
    });

    it('build() returns an array with NullConditions faulty array items are provided', () => {
        const nullConditionFaultyValue = conditionBuilder.build(['item', []]);
        expect(nullConditionFaultyValue).toHaveLength(2);
        expect(nullConditionFaultyValue[0]).toBeInstanceOf(NullCondition);
        expect(nullConditionFaultyValue[1]).toBeInstanceOf(NullCondition);
    });

    it('build() returns an array with a NullCondition if conditionSet doesnt contain valid conditionalData', () => {
        const faultyItemType = conditionBuilder.build([['item', 'item']]);
        const faultyObjectMissingFieldName = conditionBuilder.build([[{operator: '==', value: 1}]]);
        const faultyObjectMissingValue = conditionBuilder.build([[{field: 'field', operator: '=='}]]);
        const faultyObjectMissingOperator = conditionBuilder.build([[{field: 'field', value: 1}]]);
        expect(faultyItemType).toHaveLength(1);
        expect(faultyItemType[0]).toBeInstanceOf(NullCondition);
        expect(faultyObjectMissingFieldName).toHaveLength(1);
        expect(faultyObjectMissingFieldName[0]).toBeInstanceOf(NullCondition);
        expect(faultyObjectMissingValue).toHaveLength(1);
        expect(faultyObjectMissingValue[0]).toBeInstanceOf(NullCondition);
        expect(faultyObjectMissingOperator).toHaveLength(1);
        expect(faultyObjectMissingOperator[0]).toBeInstanceOf(NullCondition);
    });

    it('build() returns an array with a NullCondition fieldName doesnt exist in class param fields (fieldsObject)', () => {
        const faultyItemType = conditionBuilder.build([[createConditionSet('unknownField', 1, '==')]]);
        expect(faultyItemType).toHaveLength(1);
        expect(faultyItemType[0]).toBeInstanceOf(NullCondition);
    });

    it('build() returns an array with an OrCondition if one valid item is provided', () => {
        const faultyItemType = conditionBuilder.build([[createConditionSet()]]);
        expect(faultyItemType).toHaveLength(1);
        expect(faultyItemType[0]).toBeInstanceOf(OrCondition);
    });

    it('build() returns an array with an AndConditions if more than one valid item is provided', () => {
        const faultyItemType = conditionBuilder.build([[createConditionSet(), createConditionSet()]]);
        expect(faultyItemType).toHaveLength(1);
        expect(faultyItemType[0]).toBeInstanceOf(AndCondition);
    });

    it('build() returns an array with an AndConditions as long as one item is valid', () => {
        const faultyItemType = conditionBuilder.build([[createConditionSet(), createConditionSet('unknownField')]]);
        expect(faultyItemType).toHaveLength(1);
        expect(faultyItemType[0]).toBeInstanceOf(AndCondition);
    });

    it('build() returns an array with a NullCondition if multiple invalid items', () => {
        const faultyItemType = conditionBuilder.build([[createConditionSet('unknownField'), createConditionSet('unknownField')]]);
        expect(faultyItemType).toHaveLength(1);
        expect(faultyItemType[0]).toBeInstanceOf(NullCondition);
    });
});

function createConditionSet(field: any = 'testField', value: any = 1, operator: any = '==') {
    return {
        field: field,
        value: value,
        operator: operator,
    };
}