interface ConditionBuilderInterface {
    build(condition: any): ConditionInterface[];
}

interface ConditionInterface {
    validate(): boolean;
    getConditionFieldNames(): string[];
}

type Condition = {
    field: string;
    operator: string;
    value: any;
}