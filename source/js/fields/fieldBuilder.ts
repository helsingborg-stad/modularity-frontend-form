import Checkbox from "./field/checkbox/checkbox";
import CheckboxConditionsHandler from "./field/checkbox/checkboxConditionsHandler";
import CheckboxConditionValidator from "./field/checkbox/checkboxConditionValidator";
import NullFieldConditionsHandler from "./field/nullField/nullFieldConditionsHandler";
import NullField from "./field/nullField/nullField";
import NullFieldConditionValidator from "./field/nullField/nullFieldConditionValidator";
import Text from "./field/text/text";
import TextConditionsHandler from "./field/text/textConditionHandler";
import TextConditionValidator from "./field/text/textConditionValidator";
import Select from "./field/select/select";
import SelectConditionHandler from "./field/select/selectConditionHandler";
import SelectConditionValidator from "./field/select/selectConditionValidator";
import Radio from "./field/radio/radio";
import RadioConditionValidator from "./field/radio/radioConditionValidator";
import RadioConditionsHandler from "./field/radio/radioConditionsHandler";
import Message from "./field/message/message";
import MessageConditionsHandler from "./field/message/messageConditionHandler";

class FieldBuilder implements FieldBuilderInterface {
    private name: string = 'data-js-field-name';
    private condition: string = 'data-js-conditional-logic';

    public build(field: HTMLElement, type: string): FieldInterface {
        if (!this.validateRequiredAttributes(field)) {
            console.error('Field name and conditional logic are required');
            return this.buildNullField(field, type);
        }

        switch (type) {
            case 'checkbox':
                return this.buildCheckbox(field);
            case 'text':
            case 'email':
            case 'url':
            case 'date':
            case 'time':
            case 'number':
                return this.buildText(field);
            case 'select':
                return this.buildSelect(field);
            case 'radio':
            case 'trueFalse':
                return this.buildRadio(field);
            case 'message':
                return this.buildMessage(field);
        }

        return this.buildNullField(field, type);
    }

    private buildNullField(field: HTMLElement, type: string): FieldInterface {
        return new NullField(
            field,
            type,
            this.getFieldName(field),
            new NullFieldConditionValidator(),
            new NullFieldConditionsHandler(field, this.getFieldCondition(field))
        );
    }

    private buildMessage(field: HTMLElement): FieldInterface {
        return new Message(
            field,
            this.getFieldName(field),
            new NullFieldConditionValidator(),
            new MessageConditionsHandler(this.getFieldCondition(field))
        )
    }

    private buildRadio(field: HTMLElement): FieldInterface {
        const choices = field.querySelectorAll('input[type="radio"]') as NodeListOf<HTMLInputElement>;

        if (choices.length === 0) {
            console.error('Radio field is missing input elements');
            return this.buildNullField(field, 'radio');
        }

        return new Radio(
            field,
            choices,
            this.getFieldName(field),
            new RadioConditionValidator(),
            new RadioConditionsHandler(this.getFieldCondition(field))
        );
    }
    
    private buildSelect(field: HTMLElement): FieldInterface {
        const select = field.querySelector('select') as HTMLSelectElement;
        const options = select?.querySelectorAll('option') as NodeListOf<HTMLOptionElement>;

        if (!options || options.length === 0) {
            console.error('Select field is missing select element or options');
            return this.buildNullField(field, 'select');
        }

        return new Select(
            field,
            select,
            options,
            this.getFieldName(field),
            new SelectConditionValidator(),
            new SelectConditionHandler(this.getFieldCondition(field))
        )
    }

    private buildText(field: HTMLElement): FieldInterface {
        const input = field.querySelector(`input:is(
            [type="text"],
            [type="email"],
            [type="url"],
            [type="date"],
            [type="time"],
            [type="number"]
        )`) as HTMLInputElement;

        if (!input) {
            console.error('Text field is not an input element with type "text", "email" or "url", "date" or "time"');
            return this.buildNullField(field, 'text');
        }

        return new Text(
            field as HTMLInputElement,
            input,
            this.getFieldName(field),
            new TextConditionValidator(),
            new TextConditionsHandler(this.getFieldCondition(field))
        );
    }

    private buildCheckbox(field: HTMLElement): FieldInterface {
        const choices = field.querySelectorAll('input[type="checkbox"]') as NodeListOf<HTMLInputElement>;

        if (choices.length === 0) {
            console.error('Checkbox field is missing input elements');
            return this.buildNullField(field, 'checkbox');
        }

        return new Checkbox(
            field,
            choices,
            this.getFieldName(field),
            new CheckboxConditionValidator(),
            new CheckboxConditionsHandler(this.getFieldCondition(field))
        );
    }

    private getFieldName(field: HTMLElement): string {
        const name = field.getAttribute('data-js-field-name');
        if (!name) {
            console.error('Field is missing data-js-field-name attribute');
            return 'unknown';
        }

        return name;
    }

    private getFieldCondition(field: HTMLElement): any {
        let condition = 0;

        try {
            condition = JSON.parse(field.getAttribute('data-js-conditional-logic') as string);
        } catch (error) {
            condition = 0;
        }

        return condition;
    }

    private validateRequiredAttributes(field: HTMLElement): boolean {
        const isValid = [this.name, this.condition].every((attribute) => {
            if (!field.getAttribute(attribute)) {
                console.error(`Field is missing required attribute: ${attribute}`);
                return false;
            }
            return true;
        });

        return isValid;
    }
}

export default FieldBuilder;