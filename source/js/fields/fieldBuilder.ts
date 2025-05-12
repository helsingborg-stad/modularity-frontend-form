import RepeaterFactory from "./field/repeater/repeaterFactory";
import RadioFactory from "./field/radio/radioFactory";
import GoogleMapFactory from "./field/googleMap/googleMapFactory";
import MessageFactory from "./field/message/messageFactory";
import SelectFactory from "./field/select/selectFactory";
import CheckboxFactory from "./field/checkbox/checkboxFactory";
import NullFieldFactory from "./field/nullField/nullFieldFactory";
import FileFactory from "./field/file/fileFactory";
import TextFactory from "./field/text/textFactory";
import EmailFactory from "./field/email/emailFactory";
import UrlFactory from "./field/url/urlFactory";
import DateFactory from "./field/date/dateFactory";
import TimeFactory from "./field/time/timeFactory";
import NumberFactory from "./field/number/numberFactory";
import ImageFactory from "./field/image/imageFactory";
import TrueFalseFactory from "./field/trueFalse/trueFalseFactory";

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
            console.error('Field name and/or conditional logic are required');
            return NullFieldFactory.create(field, type, this.getFieldName(field), '0');
        }

        let fieldInstance: FieldInterface;

        switch (type) {
            case 'image':
                fieldInstance = ImageFactory.create(
                    field,
                    this.getFieldName(field),
                    this.getFieldCondition(field)
                );
                break;
            case 'file':
                fieldInstance = FileFactory.create(
                    field,
                    this.getFieldName(field),
                    this.getFieldCondition(field)
                );
                break;
            case 'checkbox':
                fieldInstance = CheckboxFactory.create(
                    field,
                    this.getFieldName(field),
                    this.getFieldCondition(field)
                );
                break;
            case 'text':
                fieldInstance = TextFactory.create(
                    field,
                    this.getFieldName(field),
                    this.getFieldCondition(field)
                );
                break;
            case 'email':
                fieldInstance = EmailFactory.create(
                    field,
                    this.getFieldName(field),
                    this.getFieldCondition(field)
                );
                break;
            case 'url':
                fieldInstance = UrlFactory.create(
                    field,
                    this.getFieldName(field),
                    this.getFieldCondition(field)
                );
                break;
            case 'date':
                fieldInstance = DateFactory.create(
                    field,
                    this.getFieldName(field),
                    this.getFieldCondition(field)
                );
                break;
            case 'time':
                fieldInstance = TimeFactory.create(
                    field,
                    this.getFieldName(field),
                    this.getFieldCondition(field)
                );
                break;
            case 'number':
                fieldInstance = NumberFactory.create(
                    field,
                    this.getFieldName(field),
                    this.getFieldCondition(field)
                );
                break;
            case 'select':
                fieldInstance = SelectFactory.create(
                    field,
                    this.getFieldName(field),
                    this.getFieldCondition(field)
                );
                break;
            case 'trueFalse':
                fieldInstance = TrueFalseFactory.create(
                    field,
                    this.getFieldName(field),
                    this.getFieldCondition(field)
                );
                break;
            case 'radio':
                fieldInstance = RadioFactory.create(
                    field,
                    this.getFieldName(field),
                    this.getFieldCondition(field)
                )
                break;
            case 'message':
                fieldInstance = MessageFactory.create(
                    field,
                    this.getFieldName(field),
                    this.getFieldCondition(field),
                );
                break;
            case 'googleMap':
                fieldInstance = GoogleMapFactory.create(
                    field,
                    this.getFieldName(field),
                    this.getFieldCondition(field),
                    this.modularityFrontendFormData,
                    this.modularityFrontendFormLang
                );
                break;
            case 'repeater':
                fieldInstance = RepeaterFactory.create(
                    this,
                    this.fieldsInitiator,
                    field,
                    this.getFieldName(field),
                    this.getFieldCondition(field)
                );
                break;
            default:
                fieldInstance = NullFieldFactory.create(
                    field,
                    type,
                    this.getFieldName(field),
                    this.getFieldCondition(field)
                );
                break;
        }

        this.fieldsObject[fieldInstance.getName()] = fieldInstance;
        return fieldInstance;
    }

    public getFieldsObject(): FieldsObject {
        return this.fieldsObject;
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