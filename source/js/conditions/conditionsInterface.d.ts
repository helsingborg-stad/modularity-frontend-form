interface ConditionBuilderInterface {
    build(condition: any): ConditionInterface[];
}

interface ConditionInterface {
    validate(): boolean;
    getConditions(): Condition[];
    getConditionFieldNames(): string[];
}

type Condition = {
    field: string;
    class: FieldInterface|null;
    operator: string;
    value?: any;
}