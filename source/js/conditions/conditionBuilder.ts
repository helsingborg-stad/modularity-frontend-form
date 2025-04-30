import AndCondition from "./condition/andCondition";
import NullCondition from "./condition/nullCondition";
import OrCondition from "./condition/orCondition";

class ConditionBuilder implements ConditionBuilderInterface {
    constructor(private fields: FieldsObject) {}

    public build(conditions: any): ConditionInterface[] {
        if (!Array.isArray(conditions) || conditions.length === 0) {
            return [new NullCondition()];
        }

        let conditionsList: ConditionInterface[] = [];
        conditions.forEach(conditionSet => {
            if (!Array.isArray(conditionSet) || conditionSet.length === 0) {
                conditionsList.push(new NullCondition());
                return;
            }

            if (conditionSet.length === 1) {
                if (this.checkConditionValidity(conditionSet[0])) {
                    conditionSet[0].class = this.fields[conditionSet[0].field];
                    conditionsList.push(new OrCondition(conditionSet[0]));
                }
            } else {
                const validConditions = conditionSet
                    .filter((condition: Condition) => this.checkConditionValidity(condition))
                    .map((condition: Condition) => ({
                        ...condition,
                        class: this.fields[condition.field]
                    }));

                if (validConditions.length > 0) {
                    conditionsList.push(new AndCondition(validConditions));
                }
            }
        });

        if (conditionsList.length > 0) {
            return conditionsList;
        }

        return [new NullCondition()];
    }

    private checkConditionValidity(condition: any): boolean {
        if (typeof condition !== 'object') {
            return false;
        }

        if (!('field' in condition) || !('operator' in condition) || !this.fields[condition.field]) {
            return false;
        }

        return true;
    }
}

export default ConditionBuilder;