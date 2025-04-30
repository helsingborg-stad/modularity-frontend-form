import { describe, expect, it } from '@jest/globals';
import FieldBuilder from './fieldBuilder';
import NullField from './field/nullField/nullField';
import Checkbox from './field/checkbox/checkbox';
import { normalModuleLoaderHook } from 'webpack-manifest-plugin/dist/hooks';

describe('Field Builder', () => {
    const fieldBuilder = new FieldBuilder();
    const div = document.createElement('div');


    it('build() a nullField if fieldElement is missing valid attributes', () => {
        const missingAttributes = fieldBuilder.build(div, 'checkbox');
        setAttributes(div, 'testField', null);
        const missingConditionAttribute = fieldBuilder.build(div, 'checkbox');
        setAttributes(div, null, '{}');
        const missingFieldName = fieldBuilder.build(div, 'checkbox');

        expect(missingAttributes).toBeInstanceOf(NullField);
        expect(missingConditionAttribute).toBeInstanceOf(NullField);
        expect(missingFieldName).toBeInstanceOf(NullField);
    });

    it('build() a nullField type isnt found', () => {
        setAttributes(div, 'testField', '[{"field":"testField","operator":"==","value":1}]');
        const missingFieldType = fieldBuilder.build(div, 'unknownField');
        expect(missingFieldType).toBeInstanceOf(NullField);
    });
    
    it('build() Builds a checkbox if valid data', () => {
        setAttributes(div, 'testField', '[{"field":"testField","operator":"==","value":1}]');
        div.innerHTML = `<input type="checkbox" name="testField" value="1">`;
        const result = fieldBuilder.build(div, 'checkbox');
        expect(result).toBeInstanceOf(Checkbox);
    });

    it('build() returns NullField if no input elements found in the container (when trying to build a checkbox)', () => {
        setAttributes(div, 'testField', '[{"field":"testField","operator":"==","value":1}]');
        div.innerHTML = `test`;
        const result = fieldBuilder.build(div, 'checkbox');
        expect(result).toBeInstanceOf(NullField);
    });

    it('build() builds correctly even if faulty conditions', () => {
        setAttributes(div, 'testField', '0/0/_<>');
        div.innerHTML = `<input type="checkbox" name="testField" value="1">`;
        const result = fieldBuilder.build(div, 'checkbox');
        expect(result).toBeInstanceOf(Checkbox);
    });
});

function setAttributes(div: HTMLElement, name: any = null, condition: any = null) {
    if (name) {
        div.setAttribute('data-js-field-name', name);
    } else {
        div.removeAttribute('data-js-field-name');
    }

    if (condition) {
        div.setAttribute('data-js-conditional-logic', condition);
    } else {
        div.removeAttribute('data-js-conditional-logic');
    }

    return div;
}