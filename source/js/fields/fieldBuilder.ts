import Checkbox from "./field/checkbox";
import NullField from "./field/nullField";

class FieldBuilder implements FieldBuilderInterface {
    private name: string = 'data-js-field-name';
    private condition: string = 'data-js-conditional-logic';

    constructor(private conditionBuilder: ConditionBuilderInterface) {
    }

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
            this.getFieldCondition(field)
        );
    }

    public buildCheckbox(field: HTMLElement): FieldInterface {
        const choices = field.querySelectorAll('input[type="checkbox"]');


        return new Checkbox(
            field,
            choices as NodeListOf<HTMLInputElement>,
            this.getFieldName(field),
            this.getFieldCondition(field)
        );
    }

    private getFieldName(field: HTMLElement): string {
        return field.getAttribute('data-js-field-name') as string;
    }

    private getFieldCondition(field: HTMLElement): ConditionInterface[] {
        let condition = 0;

        try {
            condition = JSON.parse(field.getAttribute('data-js-conditional-logic') as string);
        } catch (error) {
            condition = 0;
        }

        return this.conditionBuilder.build(condition);
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