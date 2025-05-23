// import { describe, it, expect } from '@jest/globals';
// import CheckboxConditionValidator from './checkboxConditionValidator';

// // Manual mock of CheckboxInterface
// function createMockCheckbox(selectedValues: string[]): CheckboxInterface {
//     return {
//         getSelectedChoices() {
//             return selectedValues;
//         },
//         getChoices() {
//             return [] as any;
//         },
//         getFieldContainer() {
//             return document.createElement('div');
//         },
//         init() {},
//         getName() {
//             return 'mock';
//         },
//         getValidator() {
//             return {} as any;
//         },
//         getConditionsHandler() {
//             return {} as any;
//         },
//         getConditionValidator() {
//             return {} as any;
//         }
//     };
// }

// function createCondition(operator: string, value: string = ''): Condition {
//     return {
//         operator,
//         value,
//         field: '', // Mocking 'field' property (you can adjust as needed)
//         class: null, // Mocking 'class' property (you can adjust as needed)
//     };
// }
// describe('CheckboxConditionValidator', () => {
//     it('returns true for === when value is selected', () => {
//         const validator = new CheckboxConditionValidator();
//         validator.init(createMockCheckbox(['apple', 'banana']));

//         const result = validator.validate(createCondition('===', 'banana'));
//         expect(result).toBe(true);
//     });

//     it('returns false for === when value is not selected', () => {
//         const validator = new CheckboxConditionValidator();
//         validator.init(createMockCheckbox(['apple', 'banana']));

//         const result = validator.validate(createCondition('===', 'orange'));
//         expect(result).toBe(false);
//     });

//     it('returns true for !== when value is not selected', () => {
//         const validator = new CheckboxConditionValidator();
//         validator.init(createMockCheckbox(['apple']));

//         const result = validator.validate(createCondition('!==', 'banana'));
//         expect(result).toBe(true);
//     });

//     it('returns false for !== when value is selected', () => {
//         const validator = new CheckboxConditionValidator();
//         validator.init(createMockCheckbox(['apple']));

//         const result = validator.validate(createCondition('!==', 'apple'));
//         expect(result).toBe(false);
//     });

//     it('returns true for ==empty when nothing is selected', () => {
//         const validator = new CheckboxConditionValidator();
//         validator.init(createMockCheckbox([]));

//         const result = validator.validate(createCondition('==empty'));
//         expect(result).toBe(true);
//     });

//     it('returns false for ==empty when something is selected', () => {
//         const validator = new CheckboxConditionValidator();
//         validator.init(createMockCheckbox(['1']));

//         const result = validator.validate(createCondition('==empty'));
//         expect(result).toBe(false);
//     });

//     it('returns true for !=empty when something is selected', () => {
//         const validator = new CheckboxConditionValidator();
//         validator.init(createMockCheckbox(['1']));

//         const result = validator.validate(createCondition('!=empty'));
//         expect(result).toBe(true);
//     });

//     it('returns true for > comparison', () => {
//         const validator = new CheckboxConditionValidator();
//         validator.init(createMockCheckbox(['10', '20']));

//         const result = validator.validate(createCondition('>', '15'));
//         expect(result).toBe(true);
//     });

//     it('returns false for > when no values match', () => {
//         const validator = new CheckboxConditionValidator();
//         validator.init(createMockCheckbox(['5', '10']));

//         const result = validator.validate(createCondition('>', '20'));
//         expect(result).toBe(false);
//     });

//     it('returns true for < comparison', () => {
//         const validator = new CheckboxConditionValidator();
//         validator.init(createMockCheckbox(['5', '10']));

//         const result = validator.validate(createCondition('<', '6'));
//         expect(result).toBe(true);
//     });

//     it('returns false for unknown operator', () => {
//         const validator = new CheckboxConditionValidator();
//         validator.init(createMockCheckbox(['something']));

//         const result = validator.validate(createCondition('~unknown~', 'something'));
//         expect(result).toBe(false);
//     });
// });