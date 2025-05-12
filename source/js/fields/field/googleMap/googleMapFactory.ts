import NullFieldFactory from "../nullField/nullFieldFactory";
import GoogleMapConditionsHandler from "./condition/googleMapConditionsHandler";
import GoogleMapConditionValidator from "./condition/googleMapConditionValidator";
import GoogleMap from "./googleMap";
import OpenstreetmapFactory from "./openstreetmap/openstreetmapFactory";

class GoogleMapFactory {
    public static create(
        field: HTMLElement,
        name: string,
        unstructuredConditions: any,
        modularityFrontendFormData: ModularityFrontendFormData,
        modularityFrontendFormLang: ModularityFrontendFormLang
    ): FieldInterface {
        const openstreetmapInstance = OpenstreetmapFactory.createOpenstreetmap(
            field,
            modularityFrontendFormData, 
            modularityFrontendFormLang
        );

        const hiddenField = field.querySelector('[data-js-google-map-hidden-field]') as HTMLInputElement;
        
        if (!openstreetmapInstance) {
            console.error('Failed to create map instance');
            return NullFieldFactory.create(field, 'googleMap', name, unstructuredConditions);
        }

        if (!hiddenField) {
            console.error('Failed to find hidden input field needed for Google map field.')
            return NullFieldFactory.create(field, 'googleMap', name, unstructuredConditions);
        }

        return new GoogleMap(
            field,
            hiddenField,
            openstreetmapInstance,
            name,
            new GoogleMapConditionValidator(),
            new GoogleMapConditionsHandler(unstructuredConditions)
        );
    }
}

export default GoogleMapFactory;