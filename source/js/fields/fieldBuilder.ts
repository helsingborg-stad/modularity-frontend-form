import Checkbox from "./field/checkbox/checkbox";
import CheckboxConditionsHandler from "./field/checkbox/checkboxConditionsHandler";
import CheckboxConditionValidator from "./field/checkbox/checkboxConditionValidator";
import NullFieldConditionsHandler from "./field/nullField/nullFieldConditionsHandler";
import NullField from "./field/nullField/nullField";
import NullFieldConditionValidator from "./field/nullField/nullFieldConditionValidator";

class FieldBuilder implements FieldBuilderInterface {
    private name: string = 'data-js-field-name';
    private condition: string = 'data-js-conditional-logic';

    public build(field: HTMLElement, type: string): FieldInterface {
        if (!this.validateRequiredAttributes(field)) {
            console.error('Field name and conditional is required');
            return this.buildNullField(field, type);
        }

        switch (type) {
            case 'checkbox':
                return this.buildCheckbox(field);
        }

        return this.buildNullField(field, type);
    }

    public buildNullField(field: HTMLElement, type: string): FieldInterface {
        return new NullField(
            field,
            type,
            this.getFieldName(field),
            new NullFieldConditionValidator(),
            new NullFieldConditionsHandler(field, this.getFieldCondition(field))
        );
    }

    public buildCheckbox(field: HTMLElement): FieldInterface {
        const choices = field.querySelectorAll('input[type="checkbox"]') as NodeListOf<HTMLInputElement>;

        return new Checkbox(
            field,
            choices,
            this.getFieldName(field),
            new CheckboxConditionValidator(),
            new CheckboxConditionsHandler(this.getFieldCondition(field))
        );
    }

    private getFieldName(field: HTMLElement): string {
        return field.getAttribute('data-js-field-name') as string;
    }

    private getFieldCondition(field: HTMLElement): any {
        let condition = 0;


        try {
            condition = JSON.parse(field.getAttribute('data-js-conditional-logic') as string);
        } catch (error) {
            condition = 0;
        }

        // return this.conditionBuilder.build(condition);
        return condition;
    }

    private validateRequiredAttributes(field: HTMLElement): boolean {
        [this.name, this.condition].forEach((attribute) => {
            if (!field.getAttribute(attribute)) {
                console.error(`Field is missing required attribute: ${attribute}`);
                return false;
            }
        });

        return true;
    }
}

export default FieldBuilder;