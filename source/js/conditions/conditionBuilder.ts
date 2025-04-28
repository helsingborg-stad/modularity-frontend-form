import AndCondition from "./condition/andCondition";
import NullCondition from "./condition/nullCondition";
import OrCondition from "./condition/orCondition";

class ConditionBuilder implements ConditionBuilderInterface {
    constructor(private fields: FieldsObject) {

    }

    public build(conditions: any): ConditionInterface[] {
        if (!Array.isArray(conditions) || conditions.length === 0) {
            return [new NullCondition()];
        }

        let conditionsList: ConditionInterface[] = [];
        conditions.forEach(conditionSet => {
            if (!Array.isArray(conditionSet) || conditionSet.length === 0) {
                conditionsList.push(new NullCondition());
            }

            if (conditionSet.length === 1) {
                conditionSet[0].class = this.fields[conditionSet[0].field] ?? null;
                conditionsList.push(new OrCondition(conditionSet[0]));
            } else {
                conditionSet.forEach((condition: Condition) => {
                    condition.class = this.fields[condition.field] ?? null;
                });

                conditionsList.push(new AndCondition(conditionSet));
            }
        });

        if (conditionsList.length > 0) {
            return conditionsList;
        }

        return [new NullCondition()];
    }
}

export default ConditionBuilder;