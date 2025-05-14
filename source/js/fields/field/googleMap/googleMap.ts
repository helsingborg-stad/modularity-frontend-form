import { PlaceObject } from "@helsingborg-stad/openstreetmap";

class GoogleMap implements GoogleMapInterface {
    constructor(
        private field: HTMLElement,
        private hiddenField: HTMLInputElement,
        private openstreetmapInstance: OpenstreetmapInterface,
        private name: string,
        private googleMapValidator: ConditionValidatorInterface,
        private conditionsHandler: ConditionsHandlerInterface,
        private validator: FieldValidatorInterface
    ) {
    }

    public init(conditionBuilder: ConditionBuilderInterface): void {
        this.conditionsHandler.init(this, conditionBuilder);
        this.googleMapValidator.init(this);
        this.openstreetmapInstance.init();
        this.validator.init(this);
        this.listenForMarkerEvents();
    }

    public getName(): string {
        return this.name;
    }

    public getConditionsHandler(): ConditionsHandlerInterface {
        return this.conditionsHandler;
    }

    public getConditionValidator(): ConditionValidatorInterface {
        return this.googleMapValidator;
    }

    public getValidator(): FieldValidatorInterface {
        return this.validator;
    }

    public getOpenstreetmap(): OpenstreetmapInterface {
        return this.openstreetmapInstance;
    }

    public getField(): HTMLElement {
        return this.field;
    }

    public getHiddenField(): HTMLInputElement {
        return this.hiddenField;
    }

    private listenForMarkerEvents(): void {
        this.openstreetmapInstance.addMarkerMovedListener((placeObject: PlaceObject|null) => {
            this.hiddenField.value = JSON.stringify(placeObject);
            this.conditionsHandler.checkConditions();
        });
    }
}

export default GoogleMap;