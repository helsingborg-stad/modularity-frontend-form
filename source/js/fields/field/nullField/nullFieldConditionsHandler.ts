class NullFieldConditionsHandler implements ConditionsHandlerInterface {
    constructor(private field: any, private conditions: ConditionInterface[]) {}

    public init(parent: FieldInterface, conditionsBuilder: ConditionBuilderInterface): void {
        // No implementation needed for NullField
    }

    public getConditions(): ConditionInterface[] {
        return [];
    }

    public validate(): boolean {
        return true;
    }

    public addValueChangeListener(field: FieldInterface): void {
        return;
    }
  }
  
  export default NullFieldConditionsHandler;