class GoogleMap implements GoogleMapInterface {
    constructor(
        private field: HTMLElement,
        private openstreetmapInstance: OpenstreetmapInterface,
        private name: string,
        private googleMapValidator: ConditionValidatorInterface,
        private conditionsHandler: ConditionsHandlerInterface
    ) {
    }

    public init(conditionBuilder: ConditionBuilderInterface): void {
        this.conditionsHandler.init(this, conditionBuilder);
        this.googleMapValidator.init(this);
        this.openstreetmapInstance.init();
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

    public getOpenstreetmap(): OpenstreetmapInterface {
        return this.openstreetmapInstance;
    }

    public getField(): HTMLElement {
        return this.field;
    }
}

export default GoogleMap;