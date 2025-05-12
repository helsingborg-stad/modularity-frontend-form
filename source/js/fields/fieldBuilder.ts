import Checkbox from "./field/checkbox/checkbox";
import CheckboxConditionsHandler from "./field/checkbox/checkboxConditionsHandler";
import CheckboxConditionValidator from "./field/checkbox/checkboxConditionValidator";
import NullFieldConditionsHandler from "./field/nullField/nullFieldConditionsHandler";
import NullField from "./field/nullField/nullField";
import NullFieldConditionValidator from "./field/nullField/nullFieldConditionValidator";
import Basic from "./field/basic/basic";
import BasicConditionsHandler from "./field/basic/basicConditionHandler";
import BasicConditionValidator from "./field/basic/basicConditionValidator";
import Select from "./field/select/select";
import SelectConditionHandler from "./field/select/selectConditionHandler";
import SelectConditionValidator from "./field/select/selectConditionValidator";
import Radio from "./field/radio/radio";
import RadioConditionValidator from "./field/radio/radioConditionValidator";
import RadioConditionsHandler from "./field/radio/radioConditionsHandler";
import Message from "./field/message/message";
import MessageConditionsHandler from "./field/message/messageConditionHandler";
import OpenstreetmapFactory from "./field/googleMap/openstreetmap/openstreetmapFactory";
import GoogleMap from "./field/googleMap/googleMap";
import GoogleMapConditionsHandler from "./field/googleMap/googleMapConditionsHandler";
import GoogleMapConditionValidator from "./field/googleMap/googleMapConditionValidator";
import FileConditionValidator from "./field/file/fileConditionValidator";
import FileConditionsHandler from "./field/file/fileConditionsHandler";
import RepeaterFactory from "./field/repeater/UI/repeaterFactory";

class FieldBuilder implements FieldBuilderInterface {
    private name: string = 'data-js-field-name';
    private condition: string = 'data-js-conditional-logic';
    private fieldsObject: FieldsObject = {};

    constructor(
        private fieldsInitiator: FieldsInitiatorInterface,
        private modularityFrontendFormData: ModularityFrontendFormData,
        private modularityFrontendFormLang: ModularityFrontendFormLang
    ) {}

    public build(field: HTMLElement, type: string): FieldInterface {
        if (!this.validateRequiredAttributes(field)) {
            console.error('Field name and conditional logic are required');
            return this.buildNullField(field, type);
        }

        let fieldInstance: FieldInterface;

        switch (type) {
            case 'file':
            case 'image':
                fieldInstance = this.buildFile(field);
                break;
            case 'checkbox':
                fieldInstance = this.buildCheckbox(field);
                break;
            case 'text':
            case 'email':
            case 'url':
            case 'date':
            case 'time':
            case 'number':
                fieldInstance = this.buildBasic(field);
                break;
            case 'select':
                fieldInstance = this.buildSelect(field);
                break;
            case 'radio':
            case 'trueFalse':
                fieldInstance = this.buildRadio(field);
                break;
            case 'message':
                fieldInstance = this.buildMessage(field);
                break;
            case 'googleMap':
                fieldInstance = this.buildGoogleMap(field);
                break;
            case 'repeater':
                fieldInstance = this.buildRepeater(field);
                break;
            default:
                fieldInstance = this.buildNullField(field, type);
        }

        this.fieldsObject[fieldInstance.getName()] = fieldInstance;
        return fieldInstance;
    }

    public getFieldsObject(): FieldsObject {
        return this.fieldsObject;
    }

    private buildNullField(field: HTMLElement, type: string): FieldInterface {
        return new NullField(
            field,
            type,
            this.getFieldName(field),
            new NullFieldConditionValidator(),
            new NullFieldConditionsHandler(this.getFieldCondition(field))
        );
    }

    private buildFile(field: HTMLElement): FieldInterface {
        const input = field.querySelector('input[type="file"]') as HTMLInputElement;

        if (!input) {
            console.error('Input field is not an input element with type "file"');
            return this.buildNullField(field, 'input');
        }

        return new Basic(
            field as HTMLInputElement,
            input,
            this.getFieldName(field),
            new FileConditionValidator(),
            new FileConditionsHandler(this.getFieldCondition(field))
        );
    }

    private buildRepeater(field: HTMLElement): FieldInterface {
        const addRowButton = field.querySelector('[data-js-repeater-add-row]') as HTMLButtonElement;
        if (!addRowButton) {
            console.error('Failed to find add row button for repeater');
            return this.buildNullField(field, 'repeater');
        }

        const repeater = RepeaterFactory.createRepeater(this, this.fieldsInitiator, field, addRowButton);
        
        if (!repeater) {
            console.error('Failed to create repeater UI instance');
            return this.buildNullField(field, 'repeater');
        }


        repeater?.init();
        console.log(repeater);
        return this.buildNullField(field, 'repeater');
    }

    private buildGoogleMap(field: HTMLElement): FieldInterface {
        const openstreetmapInstance = OpenstreetmapFactory.createOpenstreetmap(
            field,
            this.modularityFrontendFormData, 
            this.modularityFrontendFormLang
        );

        const hiddenField = field.querySelector('[data-js-google-map-hidden-field]') as HTMLInputElement;
        
        if (!openstreetmapInstance) {
            console.error('Failed to create map instance');
            return this.buildNullField(field, 'googleMap');
        }

        if (!hiddenField) {
            console.error('Failed to find hidden input field needed for Google map field.')
            return this.buildNullField(field, 'googleMap');
        }

        return new GoogleMap(
            field,
            hiddenField,
            openstreetmapInstance,
            this.getFieldName(field),
            new GoogleMapConditionValidator(),
            new GoogleMapConditionsHandler(this.getFieldCondition(field))
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

    private buildBasic(field: HTMLElement): FieldInterface {
        const input = field.querySelector(`input:is(
            [type="text"],
            [type="email"],
            [type="url"],
            [type="date"],
            [type="time"],
            [type="number"]
        )`) as HTMLInputElement;

        if (!input) {
            console.error('Text field is not an input element with type "text", "number", "email", "url", "date" or "time"');
            return this.buildNullField(field, 'text');
        }

        return new Basic(
            field as HTMLInputElement,
            input,
            this.getFieldName(field),
            new BasicConditionValidator(),
            new BasicConditionsHandler(this.getFieldCondition(field))
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